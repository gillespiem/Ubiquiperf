<?php
/**
 * The Child Process opens a fifo buffer for write.
 *  An Iperf object is created and it writes it's formatted output to the FIFO
 * @author gillespiem
 *
 */
class ChildProcess
{
    protected $fifo_fp;
    private $Iperf;

    public function __construct()
    {
        $this->openFifo();
        $this->Iperf = new Iperf($this->fifo_fp);
    }

    /**
     * Method to open a fifo buffer to write iPerf data to
     *  The parent will operate off of this data. 
     */
    public function openFifo()
    {


        if (!posix_mkfifo(FIFONAME, 0600))
        {
           exit(1);
        }

        $this->fifo_fp = fopen(FIFONAME, "w");
        if (!$this->fifo_fp)
        {
            echo "Error opening fifo: ".FIFONAME;
            exit(1);
        }
    }

    /**
     * Method to close our fifo buffer
     */
    public function closeFifo()
    {
        fputs($this->fifo_fp, "Child now exiting\n");
        fclose($this->fifo_fp);
    }
    
    /**
     * Method to call iPerfs client, writing END to the fifo buffer upon
     *  completion
     * @param string $iperf_options The options to pass to iperf
     * @param integer $padding The amount of delay padding either side of test.
     */
    public function runTest($iperf_options, $padding)
    {
        $this->Iperf->runTest($iperf_options, $padding);
        fputs($this->fifo_fp,"END");
    }
}
?>
