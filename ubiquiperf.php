<?php
/**
 * Ubiquiperf -  grab signal stats during the middle of an IPERF test
 *
 *
 * @todo: Start with fixing class method camels.
 * @todo: Add sanity check in throughput graphing: divide by zero, empty set, etc.
 * @todo: Cut down on our amount of command line options.
 * @author Matthew Gillespie
 */
define("FIFONAME", "/tmp/ubnt_stats_fifo_".time());
define("IPERF", "/usr/bin/iperf");
define("IPERF_PATTERN", "/\[(.*)\](.*)sec\s+(.*)\s+([0-9\.]+) Mbits\/sec/");
define("IPERF_NULL", "[  3]  0.0- 1.0 sec  2.00 MBytes  0 Mbits/sec");

/**
 * Setup autloading of classes
 */
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

/**
 * This signal handler is problematic, only leaving here for future use.
 *  Additional notes are below
 * @param integer $signal POSIX signal
 */
function signal_handler($signal) 
{
    echo "Caught a signal!\n";
}

echo "Ubiquiperf:\n";

//First ensure that we have everything we need to run.
consistency_check();

//Parse the command line options
$Opts = new Options();
$Opts->parseCli();

//@todo: This doesn't function correctly.
//  For some reason SIGCHLD isn't called upon exit of the child.
//  I can't even seemingly fire off a posix_kill() that will trigger it.
//  So for now, the Parent loop is watching for an exit code from the child
//  ala pcntl_waitpid()
//pcntl_signal(SIGCHLD, "signal_handler");

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
   
    //Create the parent object. which watches a buffer of iperf data.
    $Parent = new ParentProcess($pid, $Opts);
    $Parent->RunTest();

    exit(0);
}
else
{
    //The Child is an iperf process that writes to a fifo buffer for the parent
    //  to operate off of.
    $Child  =  new ChildProcess();
    $Child->runTest($Opts->iperf_options, $Opts->test_delay);

    exit(1);
}
