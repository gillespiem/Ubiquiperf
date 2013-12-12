<?php
define("GRAPH_WIDTH", 1024);
/**
 * 
 * Script to create throughput graphs. Requires jpgraph
 * @author: Matthew Gillespie
 */
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_line.php');

class ThroughputGraph
{
    /**
     * Method to build the Ubiquiperf graph
     *
     * @param string $datafile the CSV file which should be pipe delimited
     * @param string $outfile the PNG graph
     */
    public static function build_graph($datafile, $outfile)
    {
        
        $i=0;
        $stats = array();
        
        $fp = fopen($datafile, "r");
        
        $line = fgets($fp);
        $headers = preg_split("/\|/", $line);
        
        
        while ( !feof($fp) && $line = fgets($fp) )
        {
            $stats[$i] = new SignalProfile;
        
            $values = preg_split("/\|/", $line, 0, PREG_SPLIT_NO_EMPTY);

            for ($a=0; $a < sizeof($headers); $a++)
            {
                $headername = $headers[$a];
                $stats[$i]->{$headername} = trim($values[$a]);
            }
        
            $i++;
        
        }
        fclose($fp);

        //Build the Signal array
        //Items we're going to be graphing:
        $graph_items = array("Signal", "NoiseFloor", "Throughput", "RSSI", "txrate", "rxrate", "quality", "capacity");
        
        
        $color = array(
                            "Signal" => "#FF3300",
                            "NoiseFloor" => "#FF9900",
                            "Throughput" => "#0066FF",
                            "RSSI" => "#66FF66",
                            "txrate" => "#FE9A2E",
                            "rxrate" => "#F78181",
                            "quality" => "#00FFFF",
                            "capacity" => "#8904B1",
                            );
        
        foreach ($graph_items as $item)
        {
            $datay[$item] = array();
            foreach ($stats as $stat)
            {
                array_push($datay[$item], $stat->{$item});
            }
        }
        
        
        // Setup the graph
        $multiplier = round(GRAPH_WIDTH / sizeof($datay["Signal"])-1);
        $graphsize_x = (sizeof($datay["Signal"])-1) * $multiplier;
    
        $graphsize_x = GRAPH_WIDTH; //override
        
        $graph = new Graph( $graphsize_x , 550);
        $graph->SetScale("textlin");
        
        $theme_class=new UniversalTheme;
        
        $graph->SetTheme($theme_class);
        $graph->img->SetAntiAliasing(false);
        $graph->SetBox(false);
        
        
        
        
        $graph->img->SetAntiAliasing();
        
        $graph->yaxis->HideZeroLabel();
        $graph->yaxis->HideTicks(false,false);
        
        
        $graph->xaxis->HideLabels(true);
        
    
        //Calculate average throughput:
        $avg_throughput = 0;
        foreach ($datay["Throughput"] as $val)
        {
            $avg_throughput += $val;
        }
        $avg_throughput = sprintf("%.2f", ($avg_throughput / sizeof($datay["Throughput"])));
    
    
        // Create the first line
        foreach (array_keys($datay) as $key)
        {
            $p1 = new LinePlot($datay[$key]);
            $graph->Add($p1);
            $p1->SetColor($color[$key]);
            $p1->SetLegend($key);
        
            if ($key == "NoiseFloor")
            {
                $p1->SetFillFromYMin(-200);
                if ($key == "NoiseFloor") $p1->SetFillGradient('#FFFFFF','#FF9900');
            }
    
        }
    
    
        //Basic Text addition to the graph
        $text = new Text();
        $text->set
            (
            "AirOS {$stats[0]->FWversion}\n".
            "{$stats[0]->Frequency} / {$stats[0]->ChWidth}\n".
            "Opmode {$stats[0]->OpMode}\n".
            date("Y-m-d H:i:s", $stats[0]->Timestamp)."\n".
            "Avg Throughput: {$avg_throughput} Mbps"
            );
        $text->SetPos($graphsize_x - 200, 10);
        $text->SetBox("#ffdd22", "black");
        $text->SetShadow();
        $graph->Add($text);
    
        $graph->legend->SetFrameWeight(1);
        
        // Output line
        $graph->Stroke($outfile);
        
        self::watermark($outfile, $outfile);
    }

    /**
     * Method to watermark the Ubiquiperf graph
     *
     * @param string $infile the PNG file to watermark
     * @param string $outfile the resulting watermarked image
     */
    public static function watermark($infile, $outfile)
    {
        $ubiquiperf_logo = imagecreatefrompng('images/UbiquiPerf.png');
        $ubiquiperf_logo_width = imagesx($ubiquiperf_logo);     
        $ubiquiperf_logo_height = imagesy($ubiquiperf_logo);
                                    
        $graph = imagecreatefrompng($infile);
        $graph_size = getimagesize($infile);

        $logo_x = 394;
        $logo_y = 20;
                
        imagecopy($graph, $ubiquiperf_logo, $logo_x, $logo_y, 0, 0, $ubiquiperf_logo_width, $ubiquiperf_logo_height);
        imagepng($graph, $outfile);
                    
        imagedestroy($graph);
        imagedestroy($ubiquiperf_logo);       
    }
}
?>
