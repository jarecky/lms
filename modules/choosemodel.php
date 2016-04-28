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
	return $res;

}

//function (type, producer){

//}

$LMS->InitXajax();
$LMS->RegisterXajaxFunction(array(
	'updateModels',
));

$SMARTY->assign('producers', getProducerByType(intval($_GET['type'])));
$SMARTY->assign('xajax', $LMS->RunXajax());
$SMARTY->display("netelements/choosemodel.html");