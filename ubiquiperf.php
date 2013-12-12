<?php
/**
 * Script to grab signal stats during the middle of an IPERF test
 *
 * @author Matthew Gillespie
 */
define("FIFONAME", "/tmp/ubnt_stats_fifo_".time());
define("IPERF", "/usr/bin/iperf");
define("IPERF_PATTERN", "/\[(.*)\](.*)sec\s+(.*)\s+([0-9\.]+) Mbits\/sec/");

$Opts = new Options();
$Opts->parse_cli();


function __autoload($classname)
{
    require("lib/{$classname}.php");
}

/**
 * Various consistency checks to ensure that we can actually do our intended
 * job
 */
function consistency_check()
{
    $iperf_threads = shell_exec(IPERF." -v 2>&1");
    if (preg_match("/single threaded/", $iperf_threads))
    {
        echo "Warning: Iperf must be compiled with pthread support.\n";
        exit(1);
    }

    if (!is_dir("jpgraph"))
    {
        echo "Warning: Please download and install jpgraph (http://jpgraph.net).\n";
        exit(1);
    }
}

// This signal handler is problematic
function signal_handler($signal) 
{
    echo "Caught a signal!\n";
}

//First ensure that we have everything we need to run.
consistency_check();

//This doesn't function correctly:
pcntl_signal(SIGCHLD, "signal_handler");
pcntl_signal(SIGINT, "signal_handler");
pcntl_signal(SIGTERM, "signal_handler");

if (file_exists(FIFONAME))
{
    unlink(FIFONAME);
}

$pid = pcntl_fork();

if ($pid == -1 )
{
    die("Error Forking\n");
}
else if ($pid)
{
    //The Parent:
    // The parent watches the fifo buffer for new iperf results
    // Upon a new iperf result it grabs stats information from the
    // Access Point (or CPE)
    // Following the test, the parent generates the PNG file

    $ubnt = new UbntGatherer($Opts->statsfile, $Opts->username, $Opts->password, $Opts->ap_ip, $Opts->http_proto); 
    echo "Waiting on child {$pid}\n";
    
    do
    {     
         echo "Waiting for child to create fifo buffer...\n";
         sleep(0.5);
    } while (!file_exists(FIFONAME));
    
     $ubnt->display_header();
    
    $exitflag = FALSE;
    $fp = fopen(FIFONAME, "r");
    do
    {
         $iperf_data = fgets($fp);
     
         //@note: In reality, I should have used the -y c option in iperf which dumps CSV.
         // That's why you read the manpages before devel
         if (preg_match(IPERF_PATTERN, $iperf_data, $iperf_fields))
         {
             $ubnt->display_iperf_update($iperf_fields);
         }
    
         //This could definitely be accomplished better:
         pcntl_waitpid($pid, $returncode, WNOHANG);
         if ($returncode <> 0  )
         {
             echo "Exitflag toggled\n";
             $exitflag = TRUE;
         }
                 
    } while (!$exitflag && !feof($fp));
    
    echo "Creating graph...\n";
    ThroughputGraph::build_graph($Opts->statsfile, $Opts->png_file);
    
    
    echo "Ok, now exiting\n";
    fclose($fp);
    unlink(FIFONAME);

}
else
{
    //The Child:
    // The childwrites iperf output to a fifo, which the parent operates upon
    if (!posix_mkfifo(FIFONAME, 0600))
    {
       exit(1);
    }
    
    $fp = fopen(FIFONAME, "w");
     if (!$fp)
     {
         echo "Error opening fifo: ".FIFONAME;
         exit(1);
     }
     
    $iperf = popen(IPERF." {$Opts->iperf_options} -i 1 -f m", "r");
    while (!feof($iperf))
    {
         $iperf_data = fread($iperf, 2048);
         fputs($fp, $iperf_data);
    }
    
    fclose($iperf);
     
    fputs($fp, "Child now exiting\n");
    fclose($fp);
    exit(1);
}
