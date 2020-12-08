<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2013 LMS Developers
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

$layout['pagetitle'] = trans('Network Statistics Compacting');

set_time_limit(0);

$SMARTY->display('header.html');

echo '<H1>'.trans('Compacting Database').'</H1><PRE>';
echo trans('$a records before compacting.<BR>',$DB->GetOne('SELECT COUNT(*) FROM signals'));
flush();

if($deleted = $DB->Execute('DELETE FROM signals WHERE UNIX_TIMESTAMP(date) < ?NOW? - 2*365*24*60*60'))
{
    echo trans('$a at least one year old records have been removed.<BR>', $deleted);
    flush();
}

if($deleted = $DB->Execute('DELETE FROM signals WHERE nodeid NOT IN (SELECT id FROM nodes)'))
{
    echo trans('$a records for deleted nodes has been removed.<BR>', $deleted);
    flush();
}

$time = time();
# Dane starsze ni¿ 7 dni u¶rednione do 1 dnia
$period = $time-7*24*60*60; 
# 7 dni
$step = 24*60*60;

if ($mintime = $DB->GetOne('SELECT MIN(UNIX_TIMESTAMP(date)) FROM signals'))
{
    if ($CONFIG['database']['type'] != 'postgres')
    $multi_insert = true;
    else if (version_compare($DB->GetDBVersion(), '8.2') >= 0)
    $multi_insert = true;
    else
    $multi_insert = false;

    $nodes = $DB->GetAll('SELECT id, name FROM nodes ORDER BY name');

    foreach ($nodes as $node)
    {
        $deleted = 0;
        $inserted = 0;
        $maxtime = $period;
        $timeoffset = date('Z');
        $dtdivider = 'FLOOR((UNIX_TIMESTAMP(date)+'.$timeoffset.')/'.$step.')';

        $data = $DB->GetAll('SELECT 
    	    MAX(software) AS software, MAX(channel) AS channel, MAX(netdev) AS netdev,
    	    AVG(rxsignal) AS rxsignal, AVG(txsignal) AS txsignal,
    	    AVG(rxrate) AS rxrate, AVG(txrate) AS txrate,
    	    AVG(rxccq) AS rxccq, AVG(txccq) AS txccq,
    	    SUM(rxbytes) AS rxbytes, SUM(txbytes) AS txbytes,
    	    COUNT(date) AS count, MIN(UNIX_TIMESTAMP(date)) AS mintime, MAX(UNIX_TIMESTAMP(date)) AS maxtime
    	FROM signals WHERE nodeid = ? AND UNIX_TIMESTAMP(date) >= ? AND UNIX_TIMESTAMP(date) < ? 
    	GROUP BY nodeid, '.$dtdivider.'
    	ORDER BY mintime', array($node['id'], $mintime, $maxtime));

        if ($data) {
    	// If divider-record contains only one record we can skip it
    	// This way we'll minimize delete-insert operations count
    	// e.g. in situation when some records has been already compacted
    	foreach($data as $rid => $record) {
    	    if ($record['count'] == 1)
    		unset($data[$rid]);
    	    else
    		break;
    	}

    	// all records for this node has been already compacted
    	if (empty($data)) {
    	    echo $node['name'].': '.trans('$a - removed, $b - inserted<BR>', 0, 0);
    	    flush();
    	    continue;
    	}

    	$values = array();
    	// set start datetime of the period
    	$data = array_values($data);
    	$nodemintime = $data[0]['mintime'];

    	$DB->BeginTrans();

    	// delete old records
    	$DB->Execute('DELETE FROM signals WHERE nodeid = ? AND UNIX_TIMESTAMP(date) >= ? AND UNIX_TIMESTAMP(date) <= ?',
    	    array($node['id'], $nodemintime, $maxtime));

    	// insert new (summary) records
    	foreach ($data as $record) {
    	    $deleted += $record['count'];

    	    #if (!$record['download'] && !$record['upload'])
    	    #    continue;

    	    if ($multi_insert)
    		$values[] = sprintf('(%d, FROM_UNIXTIME(%d), %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d)',
    			$node['id'], $record['maxtime'], $record['netdev'], $record['channel'], $record['software'], $record['rxsignal'], $record['txsignal'], $record['rxrate'], $record['txrate'], $record['rxccq'], $record['txccq'], $record['rxbytes'], $record['txbytes']);
    	    else
    		$inserted += $DB->Execute('INSERT INTO signals
    		    (nodeid, date, netdev, channel, software, rxsignal, txsignal, rxrate, txrate, rxccq, txccq, rxbytes, txbytes) VALUES (?, FROM_UNIXTIME(?), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    		    array($node['id'], $record['maxtime'], $record['netdev'], $record['channel'], $record['software'], $record['rxsignal'], $record['txsignal'], $record['rxrate'], $record['txrate'], $record['rxccq'], $record['txccq'], $record['rxbytes'], $record['txbytes']));
    	}

    	if (!empty($values))
    	    $inserted = $DB->Execute('INSERT INTO signals
    		(nodeid, date, netdev, channel, software, rxsignal, txsignal, rxrate, txrate, rxccq, txccq, rxbytes, txbytes) VALUES ' . implode(', ', $values));

    	$DB->CommitTrans();

    	echo $node['name'].': '.trans('$a - removed, $b - inserted<BR>', $deleted, $inserted);
    	flush();
        }
    }
}

echo trans('$a records after compacting.',$DB->GetOne('SELECT COUNT(*) FROM signals'));
echo '</PRE>';
flush();

$SMARTY->display('footer.html');

?>
