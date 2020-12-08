<?php

$rrd_dir = ConfigHelper::getConfig('rrdstats.rrd_directory', '/tmp'); 
$rrdtool = ConfigHelper::getConfig('rrdstats.rrdtool_binary', '/usr/bin/rrdtool');

$filerrd = $rrd_dir . "/signals/" . intval($_GET['id']) . ".rrd";

if (!file_exists($filerrd))
	die;

$period = isset($_GET['period']) ? $_GET['period'] : '7d';
if (!preg_match('/^[0-9]+d$/', $period))
	$period = '7d';

$titles = array(
	"1d" => trans("day"),
	"7d" => trans("week"),
	"60d" => trans("2 months"),
	"730d" => trans("2 years"),
	"1800d" => trans("5 lat"),
);
$timestamp = "now-$period";
$title = $titles[$period];

$quote = '"';

#if ($_GET['ext'] == 1)
    $opts = array(
	"--imgformat=PNG",
	"-e now",
	"-s $timestamp",
	"-t ".$quote."Siła i jakość sygnału - $title".$quote,
	"-r",
	"-b 1000",
	"-h 150",
	"-w 730",
	"-u 120",
	"-l -100",
	"-n LEGEND:7",
	"-n TITLE:8",
	"--slope-mode",
	"-cBACK#DFD5BD",
	"-cSHADEA#CEBD9B",
	"-cSHADEB#CEBD9B",
	"-cCANVAS#efe5cd",
	"DEF:txsignal=".$quote."$filerrd".$quote.":txsignal:AVERAGE",
	"DEF:txsignal_min=".$quote."$filerrd".$quote.":txsignal:MIN",
	"DEF:txsignal_max=".$quote."$filerrd".$quote.":txsignal:MAX",
	"DEF:rxsignal=".$quote."$filerrd".$quote.":rxsignal:AVERAGE",
	"DEF:rxsignal_min=".$quote."$filerrd".$quote.":rxsignal:MIN",
	"DEF:rxsignal_max=".$quote."$filerrd".$quote.":rxsignal:MAX",
	"DEF:txccq=".$quote."$filerrd".$quote.":txccq:AVERAGE",
	"DEF:txccq_min=".$quote."$filerrd".$quote.":txccq:MIN",
	"DEF:txccq_max=".$quote."$filerrd".$quote.":txccq:MAX",
	"DEF:rxccq=".$quote."$filerrd".$quote.":rxccq:AVERAGE",
	"DEF:rxccq_min=".$quote."$filerrd".$quote.":rxccq:MIN",
	"DEF:rxccq_max=".$quote."$filerrd".$quote.":rxccq:MAX",
	"VDEF:rxsavg=rxsignal,AVERAGE",
	"VDEF:txsavg=txsignal,AVERAGE",
	"VDEF:rxcavg=rxccq,AVERAGE",
	"VDEF:txcavg=txccq,AVERAGE",

	#"LINE2:rxsavg#760a0a",
	"LINE2:rxsignal#f15858:".$quote."Rx Signal".$quote,
	"GPRINT:rxsignal:MIN:".$quote."Min\: %4.1lfdBm ".$quote,
	"GPRINT:rxsignal:MAX:".$quote."Max\: %4.1lfdBm ".$quote,
	"GPRINT:rxsignal:LAST:".$quote."Last\: %4.1lfdBm ".$quote,

	#"LINE2:txsavg#006644",
	"LINE2:txsignal#00c080:".$quote."Tx Signal".$quote,
	"GPRINT:txsignal:MIN:".$quote."Min\: %4.1lfdBm ".$quote,
	"GPRINT:txsignal:MAX:".$quote."Max\: %4.1lfdBm ".$quote,
	"GPRINT:txsignal:LAST:".$quote."Last\: %4.1lfdBm \\n".$quote,

	#"LINE2:rxcavg#002080",
        "LINE2:rxccq#3366ff:".$quote."Rx CCQ   ".$quote,
        "GPRINT:rxccq:MIN:".$quote."Min\: %5.0lf%%   ".$quote,
        "GPRINT:rxccq:MAX:".$quote."Max\: %5.0lf%%   ".$quote,
        "GPRINT:rxccq:LAST:".$quote."Last\: %5.0lf%%   ".$quote,

	#"LINE2:txcavg#993300",
        "LINE2:txccq#ff9966:".$quote."Tx CCQ   ".$quote,
        "GPRINT:txccq:MIN:".$quote."Min\: %5.0lf%%   ".$quote,
        "GPRINT:txccq:MAX:".$quote."Max\: %5.0lf%%   ".$quote,
        "GPRINT:txccq:LAST:".$quote."Last\: %5.0lf%%\\n".$quote,

	"HRULE:-90#aa0000:",
	"HRULE:-60#00aa00:",
	"HRULE:0#aaaaaa:"
    );
#else
#    $opts = array(
#      "--imgformat=PNG",
#      "-e now",
#      "-s $timestamp",
#      "-r",
#      "-b 1000",
#      "-h 200",
#      "-w 600",
#      "-u 120",
#      "-n LEGEND:7",
#      "--slope-mode",
#      "-cBACK#DFD5BD",
#      "-cSHADEA#DCDCDC",
#      "-cSHADEB#DCDCDC",
#      "-cCANVAS#efe5cd",
#      "DEF:txsignal=".$quote."$filerrd".$quote.":txsignal:AVERAGE",
#      "DEF:txsignal=".$quote."$filerrd".$quote.":txsignal:AVERAGE",
#
#      "LINE2:txsignal#f15858:",
#      "LINE2:txsignal#00c080:",
#      "GPRINT:txsignal:LAST:".$quote."Last\: ONU Rx Power\: %.1lfdBm   ".$quote,
#      "GPRINT:txsignal:LAST:".$quote."OLT Rx Power\: %.1lfdBm    ".$quote,
#
#      "HRULE:-30#aaaaaa:",
#      "HRULE:-20#aaaaaa:",
#      "HRULE:-10#aaaaaa:",
#      "HRULE:0#aaaaaa:"
#    );

putenv('LC_TIME=pl_PL.UTF-8');
$cmd = $rrdtool . ' graph - ' . implode(" ", $opts);
$imgstring = shell_exec($cmd);
$source = imagecreatefromstring($imgstring);

if ($_GET['ext'] == 1) {
	header("Content-type: image/png");
	imagepng($source);
} else {
	$sx = 0;
	$sy = 0;
	$ex = 811;
	$ey = 224;
	$nw = $ex - $sx;
	$nh = $ey - $sy;
	$thumb = imagecreatetruecolor($nw, $nh);
	imagecopyresized($thumb, $source, 0, 0, $sx, $sy, $nw, $nh, $nw, $nh);

	header("Content-type: image/png");
	imagepng($thumb);
}

imagedestroy($source);

?>
