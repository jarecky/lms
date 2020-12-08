<?php

if (isset($_GET['id'])) {
        $nodeid=$_GET['id'];
} elseif ($SESSION->is_set('nodeid')) {
        $SESSION->restore('nodeid', $nodeid);
}
$SESSION->save('nodeid', $nodeid);


$layout['pagetitle']='Historia sygnałów: '.$LMS->GetNodeName($nodeid);

if (isset($_GET['rrd'])) {
	$SMARTY->assign('id',$_GET['id']);
	$SMARTY->display('node/nodesignalsgraph.html');
} else {
	$page  = !isset($_GET['page']) ? 1 : intval($_GET['page']);
	$limit = intval(ConfigHelper::getConfig('phpui.pagelimit', 100));
	$offset = ($page - 1) * $limit;
	$total=intval($DB->GetOne("SELECT count(*) FROM signals WHERE nodeid=".$nodeid));

	if ($signallist = $DB->GetAll('SELECT * FROM signals WHERE nodeid='.$nodeid.' ORDER BY date DESC LIMIT '.$offset.','.$limit)) {
		foreach ($signallist as $idx => $row) {
			$signallist[$idx]['ap']=$DB->GetOne("SELECT name FROM netdevices WHERE id=?",array($row['netdev']));
			list($data,$units)=setunits($row['rxbytes']);
			$signallist[$idx]['rxbytes']=number_format($data,2,',',' ').' '.$units;
			list($data,$units)=setunits($row['txbytes']);
			$signallist[$idx]['txbytes']=number_format($data,2,',',' ').' '.$units;
			$signallist[$idx]['date']=substr($row['date'],0,16);
		}

	}
	$pagination = LMSPaginationFactory::getPagination($page, $total, $limit, ConfigHelper::checkConfig('phpui.short_pagescroller'));

	$SMARTY->assign('pagination',$pagination);
	$SMARTY->assign('signallist',$signallist);

	$SMARTY->display('node/nodesignals.html');
}

?>
