Ubiquiperf
==========

Graph Ubiquiti AirOS stats during an Iperf test.

This is a quick script to perform an iperf to a remote server while grabbing various statistics
from a Ubiquiti Access Point or CPE. I just wanted a quick and dirty way to see iperf data
aligned with signal stats, etc at the same time. It uses jpgraph (http://jpgraph.net) to plot everything 
once the tests are complete. You'll need to download it and install or symlink it into the 
main directory (/jpgraph).

At the present time it only runs as a client, so effectively it's an upload test. Of course,
you can always run it from the other side as well.

## Example graph

![Ubiquiperf Graph](https://raw.github.com/gillespiem/Ubiquiperf/master/images/5ghz.home.png)

## Known issues

If you're running a single threaded instance of iperf, the output of the client is problematic 
and will not work. To fix that, recompile iperf with pthread support. 

## Example usage

```bash
php ./ubiquiperf.php -s <iperf server> -a <AP IP> -u <AP Username> -p <AP/CPE Password> -d <test duration> -c <CSV OUTPUT> -g <PNG OUTPUT> -h <http|https>
```

Example graphs are found in the images/ directory.

If you have any questions, feel free to contact me.
