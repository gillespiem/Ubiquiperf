<?php
class Iperf
{
    private $fifo_fp;
    
    public function __construct($fifo_fp)
    {
        $this->fifo_fp = $fifo_fp;
    }
    
    /**
     * Method to perform an iperf test, writing output to our fifo buffer
     * @param string $iperf_options CLI options for iperf.
     */
    public function client($iperf_options)
    {
        echo "Beginning iperf test...\n";
        $iperf_fp = popen(IPERF." {$iperf_options} -i 1 -f m", "r");

        while (!feof($iperf_fp))
        {
             $iperf_data = fread($iperf_fp, 2048);
             fputs($this->fifo_fp, $iperf_data);
        }
        
        fclose($iperf_fp);
    }

    /**
     * Method to provide a few seconds of data to our output, prior
     *  to beginning the iperf test.
     * @param integer $padding Amount of delay in seconds
     */
    public function delay($padding)
    {   
        echo "Delaying...\n";
        $start_time = time();
        do
        {
            fputs($this->fifo_fp, IPERF_NULL."\n");
            sleep(2);
        } while (time() - $padding < $start_time);

    }

    /**
     * Method to call the client and delay routines, if needed
     * @param string $iperf_options CLI options for iperf
     * @param integer $padding Amount of delay in seconds
     */
    public function runTest($iperf_options, $padding)
    {
        if ($padding > 0)
        {
            $this->delay($padding);
            $this->client($iperf_options);
            $this->delay($padding);
        }
        else
        {
            $this->Client($iperf_options);
        }
    }

}
?>
