Ubiquiperf
==========

Graph Ubiquiti AirOS stats during an Iperf test.

This is a quick script to perform an iperf to a remote server while grabbing various statistics
from a Ubiquiti Access Point or CPE. I just wanted a quick and dirty way to see iperf data
aligned with signal stats, etc at the same time. It uses jpgraph (jpgraph.net) to plot everything 
once the tests are complete. You'll need to download it and install or symlink it into the 
main directory (/jpgraph).

At the present time it only runs as a client, so effectively it's an upload test. Of course,
you can always run it from the other side as well.

Known issues:

If you're running a single threaded instance of iperf, the output of
the client is problematic and will not work.

You can determine this by the output of iperf -v:
	iperf version 2.0.5 (08 Jul 2010) single threaded is problematic and doesn't work

OR if you see this zero output when running the iperf by hand
(ala /usr/bin/iperf -c storage0.ctinetworks.com -i 1 -t 300 -f m):

[  3] -1386783776.1--1386783775.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783775.1--1386783774.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783774.1--1386783773.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783773.1--1386783772.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783772.1--1386783771.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783771.1--1386783770.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783770.1--1386783769.1 sec  0.00 MBytes  0.00 Mbits/sec
[  3] -1386783769.1--1386783768.1 sec  0.00 MBytes  0.00 Mbits/sec

To fix that, recompile iperf.

Example usage:

php ./ubiquiperf.php -s <iperf server> -a <AP IP> -u <AP Username> -p <AP/CPE Password> -d <test duration> -c <CSV OUTPUT> -g <PNG OUTPUT> -h <http|https>


Example graphs are found in the images/ directory.

If you have any questions, feel free to contact me:
gillespiem@braindeadprojects.com
