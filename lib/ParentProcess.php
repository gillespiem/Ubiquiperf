<?php
/**
 * The Parent Process opens the FIFO buffer created by the Child process.
 *  It then reads the Iperf data from the buffer, and pulls UBNT Air OS stats
 *  from the HTTP or HTTPS interface using a Ubnt object.
 * @author gillespiem
 *
 */
class ParentProcess
{
    protected $fifo_fp;
    private $exitflag, $pid;
    private $Opts, $Ubnt;

    public function __construct($pid, $Opts)
    {
        $this->pid = $pid;
        $this->exitflag = FALSE;
        $this->Opts = $Opts;
        $this->Ubnt = new UbntGatherer($Opts->statsfile,
                $Opts->username,
                $Opts->password,
                $Opts->ap_ip,
                $Opts->http_proto);
    }

    /**
     * Method to wait for fifo buffer to be created by the child
     */
    public function waitForFifo()
    {
        echo "Waiting on child {$this->pid}\n";

        do
        {
        echo "Waiting for child to create fifo buffer...\n";
                sleep(1);
        } while (!file_exists(FIFONAME));
        }

    /**
     * Method to open fifo buffer created by the child process for read
     */
    public function openFifo()
    {
        $this->fifo_fp = fopen(FIFONAME, "r");
    }

    /**
     * Method to close the fifo buffer.
     */
    public function closeFifo()
    {
        echo "Ok, now exiting\n";
        fclose($this->fifo_fp);
        unlink(FIFONAME);
    }

    /**
     * Method to watch the fifo buffer for iperf data
     */
    public function tailFifo()
    {
        $this->Ubnt->displayHeader();
        do
        {

        $iperf_data = fgets($this->fifo_fp);
         
        //@note: In reality, I should have used the -y c option in iperf which dumps CSV.
        // That's why you read the manpages before devel
        
        //@todo: If I continue to watch for "END", how I'm doing now,
        //          I could potentially send "NULL" instead of the
        //          bunk IPERF_NULL.
        if (preg_match(IPERF_PATTERN, $iperf_data, $iperf_fields))
        {
            $this->Ubnt->displayIperfUpdate($iperf_fields);
        }

        //This could definitely be accomplished better:
        pcntl_waitpid($this->pid, $returncode, WNOHANG);
        if ($returncode <> 0  )
            {
            echo "Exitflag toggled\n";
            $this->exitflag = TRUE;
        }
         
        //} while (!$this->exitflag && !feof($this->fifo_fp));
        } while ($iperf_data !== "END");
    }

    /**
     * Method to create a graph out of our newly generated data.
     */
    public function createGraph()
    {
        echo "Creating graph... {$this->Opts->statsfile} {$this->Opts->png_file}\n";
        ThroughputGraph::buildGraph($this->Opts->statsfile, $this->Opts->png_file);
    }

    /**
     * Method to call routines needed to execute a test.
     */
    public function RunTest()
    {
        $this->waitForFifo();
        $this->openFifo();
        $this->tailFifo();
        $this->createGraph();
        $this->closeFifo();
    }
}
?>