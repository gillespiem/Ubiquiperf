<?php
error_reporting(E_ERROR); 

/**
 * Class to wrap the curl object for things specific to Ubiquiti
 * 
 * @author Matthew Gillespie
 */
class UbntGatherer extends Curl
{
    private $stats_fp, $username, $password, $ap_ip, $http_proto;

    /**
     * Constructor method
     *
     * @param string $statsfile CSV output file
     * @param string $username UBNT username for AP/CPE
     * @param string $ap_ip IP address of AP/CPE to pull json stats from
     * @param string $http_proto [http|https]
     */
    public function __construct($statsfile, $username, $password, $ap_ip, $http_proto)
    {
        $this->username = $username;
        $this->password = $password;
        $this->ap_ip = $ap_ip;
        $this->http_proto = $http_proto;

        $this->setReferrer("{$this->http_proto}://{$this->ap_ip}/login.cgi");
        $this->setUserAgent("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1");
        $this->setCookieFile("cookie.txt");

        $this->open_statsfile($statsfile);
    }

    public function __destruct()
    {
        $this->close_statsfile();
    }

    /**
     * Method to open the CSV file for write
     * 
     * @param string $stats_file CSV file to write to
     */
    private function open_statsfile($stats_file)
    {
        $this->stats_fp = fopen($stats_file, "w");
        if (!$this->stats_fp)
        {
            echo "Error opening stats CSV file\n";
            exit(1);
        }
    }

    /**
     * Method to close the CSV file
     */
    private function close_statsfile()
    {
        fclose($this->stats_fp);
    }

    /**
     * Method to login and grab stats from an AirOS device
     */
    public function grab_signalstats()
    {
        
        $this->postdata = array("username" => $this->username,
                     "password" => $this->password,
                     "uri" => "/status.cgi");

        $ubnt_stats = json_decode($this->Post("{$this->http_proto}://{$this->ap_ip}/login.cgi"));
        return $ubnt_stats;
    }

    /**
     * Method to log (and display) CSV data live
     * 
     * @param string $string pipe delimited data to log
     */
    private function log($string)
    {
        echo $string;
        fputs($this->stats_fp, $string);
    }

    /**
     * Method to display the CSV header
     */
    public function display_header()
    {
        $this->log("RSSI|Signal|".
                      "NoiseFloor|ChWidth|".
                      "Throughput|FWversion|".
                      "Frequency|Channel|".
                      "Ack|Distance|".
                      "ccq|txrate|".
                      "rxrate|quality|".
                      "capacity|OpMode|".
                      "ChainRSSI0|ChainRSSI1|txretries|".
                      "Timestamp|\n");
    }

    /**
     * Method to display IPERF and UBNT stats
     * 
     * @param array $iperf_fields preg_split() fields from iperf stats
     */
    public function display_iperf_update($iperf_fields)
    {
        $ubnt_info = $this->grab_signalstats();

        if (!is_null($ubnt_info))
        {
            $this->log("{$ubnt_info->wireless->rssi}|{$ubnt_info->wireless->signal}|".
                          "{$ubnt_info->wireless->noisef}|{$ubnt_info->wireless->chwidth}|".
                          "{$iperf_fields[4]}|{$ubnt_info->host->fwversion}|".
                          "{$ubnt_info->wireless->frequency}|{$ubnt_info->wireless->channel}|".
                          "{$ubnt_info->wireless->ack}|{$ubnt_info->wireless->distance}|".
                          "{$ubnt_info->wireless->ccq}|{$ubnt_info->wireless->txrate}|".
                          "{$ubnt_info->wireless->rxrate}|{$ubnt_info->wireless->polling->quality}|".
                          "{$ubnt_info->wireless->polling->capacity}|{$ubnt_info->wireless->opmode}|".
                          "{$ubnt_info->wireless->chainrssi[0]}|{$ubnt_info->wireless->chainrssi[1]}|".
                          "{$ubnt_info->wireless->stats->tx_retries}|".
                          time()."|\n");
        }
    }
}
?>

