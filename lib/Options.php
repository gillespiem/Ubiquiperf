<?php
/**
 * Dumb class to simply handle some command line options
 */
class Options
{

    public $duration, $username, $password, $ap_ip, $statsfile, $png_file, $http_proto,
                $iperf_options, $test_delay;

    /**
     * Method to parse CLI options ala getopt()
     *
     */
    public function parseCli()
    {   
        //Command line handling
        $opts = "a:u:p:c:g:h:d:y:";
        $clientopts = "s:";
        $serveropts = "l";
        
        $options = getopt($opts.$clientopts.$serveropts);
        
        //Ensure we have our options correct
        foreach (preg_split("/:/", $opts,0,PREG_SPLIT_NO_EMPTY) as $val)
        { 
            if (!isset($options[$val]))
            {
                echo "Usage: [-s <iperf server> | -l ] -a <ap ip> -d <duration> -u <username> -p <password> -h <http|https> -c <csv outfile> -g <png outfile> -y <delay padding>\n";
                echo "Hint: Try setting -{$val}\n";
                exit(1);
            }
        }
        
        if (isset($options["s"]))
        {
            $this->iperf_options = " -c {$options["s"]} -t {$options["d"]} ";
        }
        elseif (isset($options["l"]))
        {
            $this->iperf_options = " -s ";
            echo "Warning: Server mode aren't currently supported.\n";
        
            exit(1);
        }
        else
        {
            echo "Warning: You must specify either -s (with -d) or -l options.\n";
        }

        $this->duration = $options["d"];
        $this->username = $options["u"];
        $this->password = $options["p"];
        $this->ap_ip = $options["a"];
        $this->statsfile = $options["c"];
        $this->png_file = $options["g"];
        $this->http_proto = $options["h"];
        $this->test_delay = $options["y"];
    
    }
    
}

?>
