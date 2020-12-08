#!/usr/bin/php
<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2015 LMS Developers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, 
 *  USA.
 *
 *  $Id$
 */

ini_set('error_reporting', E_ALL&~E_NOTICE);

$parameters = array(
	'C:' => 'config-file:',
	'q' => 'quiet',
	't' => 'test',
	'd' => 'debug',
	'h' => 'help',
	'v' => 'version',
);

foreach ($parameters as $key => $val) {
	$val = preg_replace('/:/', '', $val);
	$newkey = preg_replace('/:/', '', $key);
	$short_to_longs[$newkey] = $val;
}
$options = getopt(implode('', array_keys($parameters)), $parameters);
foreach ($short_to_longs as $short => $long)
	if (array_key_exists($short, $options)) {
		$options[$long] = $options[$short];
		unset($options[$short]);
	}

if (array_key_exists('version', $options)) {
	print <<<EOF
lms-signals.php
(C) 2001-2015 LMS Developers

EOF;
	exit(0);
}

if (array_key_exists('help', $options)) {
	print <<<EOF
lms-signals.php
(C) 2001-2015 LMS Developers

-C, --config-file=/etc/lms/lms.ini      alternate config file (default: /etc/lms/lms.ini);
-h, --help                      print this help and exit;
-t, --test			test only - dont update database
-d, --debug			print debugging information
-v, --version                   print version info and exit;
-q, --quiet                     suppress any output, except errors

EOF;
	exit(0);
}

$test = array_key_exists('test', $options);
$debug = array_key_exists('debug', $options);

$quiet = array_key_exists('quiet', $options);
if (!$quiet) {
	print <<<EOF
lms-signals.php
(C) 2001-2015 LMS Developers

EOF;
}

if (array_key_exists('config-file', $options))
	$CONFIG_FILE = $options['config-file'];
else
	$CONFIG_FILE = DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'lms' . DIRECTORY_SEPARATOR . 'lms.ini';

if (!$quiet)
	echo "Using file ".$CONFIG_FILE." as config." . PHP_EOL;

if (!is_readable($CONFIG_FILE))
	die("Unable to read configuration file [".$CONFIG_FILE."]!" . PHP_EOL);

define('CONFIG_FILE', $CONFIG_FILE);

$CONFIG = (array) parse_ini_file($CONFIG_FILE, true);

// Check for configuration vars and set default values
$CONFIG['directories']['sys_dir'] = (!isset($CONFIG['directories']['sys_dir']) ? getcwd() : $CONFIG['directories']['sys_dir']);
$CONFIG['directories']['lib_dir'] = (!isset($CONFIG['directories']['lib_dir']) ? $CONFIG['directories']['sys_dir'] . DIRECTORY_SEPARATOR . 'lib' : $CONFIG['directories']['lib_dir']);

define('SYS_DIR', $CONFIG['directories']['sys_dir']);
define('LIB_DIR', $CONFIG['directories']['lib_dir']);
define('VENDOR_DIR', $CONFIG['directories']['vendor_dir']);

// Load autoloader
$composer_autoload_path = VENDOR_DIR . DIRECTORY_SEPARATOR . 'autoload.php';
if (file_exists($composer_autoload_path)) {
    require_once $composer_autoload_path;
} else {
    die("Composer autoload not found. Run 'composer install' command from LMS directory and try again. More informations at https://getcomposer.org/");
}


// Do some checks and load config defaults
require_once(LIB_DIR . DIRECTORY_SEPARATOR . 'config.php');

// Init database

$DB = null;

try {
	$DB = LMSDB::getInstance();
} catch (Exception $ex) {
	trigger_error($ex->getMessage(), E_USER_WARNING);
	// can't working without database
	die("Fatal error: cannot connect to database!" . PHP_EOL);
}

/* ****************************************
   Good place for config value analysis
   ****************************************/


// Include required files (including sequence is important)

require_once(LIB_DIR . DIRECTORY_SEPARATOR . 'common.php');
require_once(LIB_DIR . DIRECTORY_SEPARATOR . 'language.php');
include_once(LIB_DIR . DIRECTORY_SEPARATOR . 'definitions.php');
require_once(LIB_DIR . DIRECTORY_SEPARATOR . 'unstrip.php');
require_once(LIB_DIR . DIRECTORY_SEPARATOR . 'SYSLOG.class.php');

if (ConfigHelper::checkConfig('phpui.logging') && class_exists('SYSLOG'))
	$SYSLOG = new SYSLOG($DB);
else
	$SYSLOG = null;

// Initialize Session, Auth and LMS classes

$AUTH = NULL;
$LMS = new LMS($DB, $AUTH, $SYSLOG);
$LMS->ui_lang = $_ui_language;
$LMS->lang = $_language;

define('RRD_DIR', ConfigHelper::getConfig('rrdstats.rrd_directory', '/tmp'));
define('RRDTOOL_BINARY', ConfigHelper::getConfig('rrdstats.rrdtool_binary', '/usr/bin/rrdtool'));

$rrdtool_process = proc_open(RRDTOOL_BINARY . ' -',
        array(
                0 => array('pipe', 'r'),
                1 => array('file', '/dev/null', 'w'),
                2 => array('file', '/dev/null', 'w'),
        ),
        $rrdtool_pipes
);
if (!is_resource($rrdtool_process))
        die("Couldn't open " . RRDTOOL_BINARY . "!" . PHP_EOL);

$rs=$DB->GetAll("SELECT * FROM netradiosectors ORDER BY name ASC");
foreach ($rs AS $id => $radiosector) {
	#if ($debug) echo $radiosector['name']."\n";
	$netdev=$LMS->GetNetDev($radiosector['netdev']);
	if (preg_match('/mikrotik/i',$netdev['producer'])) {
		$netdevips = $LMS->GetNetDevIPs($radiosector['netdev']);
		$ip=$netdevips['0']['ip'];
		if (preg_match('/.*-(.*)$/',$radiosector['name'],$x))
			$interface=$x[1];
		else
			$interface='all';
			
		if ($debug) echo "Urządzenie ".$netdev['name']."($ip): $interface\n";
		$mt=new Mikrotik($ip,5);
		$mt->debug=false;
		$channel=$mt->GetChannel($interface);
		$connected=$mt->GetRadiosectorConnected($interface);
		foreach ($connected AS $id => $dev) {
			$nodeid=$LMS->GetNodeIDByMAC($dev['mac-address']);
			$dev['rx-rate']=preg_replace('/Mbps.*/','',$dev['rx-rate']);
			$dev['tx-rate']=preg_replace('/Mbps.*/','',$dev['tx-rate']);
			$dev['signal-strength']=preg_replace('/@.*$/','',$dev['signal-strength']);
			if ($dev['tx-signal-strength']=='') $dev['tx-signal-strength']=0;
			if ($dev['rx-ccq']=='') $dev['rx-ccq']=0;
			if ($dev['routeros-version']=='') $dev['routeros-version']='';
			$bytes=preg_split('/,/',$dev['bytes']);
			$args=array(strftime('%Y-%m-%d %H:%M:%S'),$nodeid,$netdev['id'],$channel,$dev['routeros-version'],$dev['signal-strength'],$dev['tx-signal-strength'],$dev['rx-rate'],$dev['tx-rate'],$dev['rx-ccq'],$dev['tx-ccq'],$bytes[0],$bytes[1]);
			if ($debug) print_r($args);
			#date nodeid netdev channel software rxsignal txsignal rxrate txrate rxccq txccq rxbytes txbytes
			if (!$test) {
				$rrd_file = RRD_DIR . DIRECTORY_SEPARATOR . "signals/".$nodeid.".rrd";
				if (!is_file($rrd_file)) {
					$cmd = "create $rrd_file --step 3600"
						. " DS:rxsignal:GAUGE:7200:-100:0"
						. " DS:txsignal:GAUGE:7200:-100:0"
						. " DS:rxccq:GAUGE:7200:0:100"
						. " DS:txccq:GAUGE:7200:0:100"
						. " RRA:AVERAGE:0.5:1:8760" // przez rok bez agregacji
						. " RRA:AVERAGE:0.5:24:1100"  // przez 3 lata agregacja do dnia
						. " RRA:AVERAGE:0.5:168:260" // przez 5 lat agregacja tygodnia
						. " RRA:MIN:0.5:1:8760"
						. " RRA:MIN:0.5:24:1100"
						. " RRA:MIN:0.5:168:260"
						. " RRA:MAX:0.5:1:800"
						. " RRA:MAX:0.5:24:1100"
						. " RRA:MAX:0.5:168:260";
					fwrite($rrdtool_pipes[0], $cmd . PHP_EOL);
				}
				$cmd = "update $rrd_file";
				fwrite($rrdtool_pipes[0],"update $rrd_file N:".$dev['signal-strength'].":".$dev['tx-signal-strength'].":".$dev['rx-ccq'].":".$dev['tx-ccq']."\n");
				$DB->Execute("INSERT INTO signals VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",$args);
			}
		}
	} elseif ($debug) {
		echo "Producentem ".$netdev['name']." jest ".$netdev['producer']." a nie Mikrotik!\n";
	}
}



#$mt = new RouterosAPI();






?>
