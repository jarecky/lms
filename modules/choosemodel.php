<?php
function getProducerByType($type){
	$DB=LMSDB::getInstance();
	$q="SELECT distinct(p.id), p.name FROM netdeviceproducers p 
		LEFT JOIN netdevicemodels m ON p.id=m.netdeviceproducerid
		WHERE m.type=".$type;
	$producers = $DB->getAll($q);
	return $producers;
}

function updateModels($type, $producer){
	$DB=LMSDB::getInstance();
	$res = new xajaxResponse();
	$q="SELECT m.id, m.name FROM netdevicemodels m WHERE m.type=".$type." AND m.netdeviceproducerid=".$producer;
	$producers = $DB->getAll($q);
	$res->script("var d=document.getElementById('model'); d.options.length=0;");
	$res->script("var d=document.getElementById('model'); d.options[d.options.length]=new Option('".trans('Select option')."','-1');");
	foreach($producers as $p){
		$res->script("var d=document.getElementById('model'); d.options[d.options.length]=new Option('".$p['name']."','".$p['id']."');");
	}
	$res->assign('model','style.display','table-row-group');
	return $res;

}
function updatePortlist($modelid){
	global $NETCONNECTORS, $NETPORTTYPES;
	$DB=LMSDB::getInstance();
	$res = new xajaxResponse();
	$ports = $DB->getAll("SELECT id, label, connector, port_type FROM netdeviceschema WHERE model=".$modelid." ORDER by connector, label");
	$res->assign('porttable','innerHTML','');
	if($ports)
	foreach($ports as $p){
	  $list.='<tr width="100%"><td style="white-space: nowrap">'.trans('Label:').$p['label'].'</td><td>'.trans('Medium:').$NETPORTTYPES[($p['port_type'])].' '.trans('Connector:').$NETCONNECTORS[($p['connector'])].'</td></tr>';
	}
	else
	$list.='<tr><td colspan="2">'.trans('No such ports.').'</td></tr>';
	$res->assign('porttable','innerHTML',$list);
	$res->assign('porttable','style.display','table-row-group');

	return $res;
}



$LMS->InitXajax();
$LMS->RegisterXajaxFunction(array(
	'updateModels','updatePortlist',
));

$SMARTY->assign('producers', getProducerByType(intval($_GET['type'])));
$SMARTY->assign('xajax', $LMS->RunXajax());
$SMARTY->display("netelements/choosemodel.html");