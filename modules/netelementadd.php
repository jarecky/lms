<?php
$layout['pagetitle'] = trans('New Device <i>example</i>');

//funkcje wlasne
$producers = $DB->GetAll('
    SELECT id, name
    FROM netdeviceproducers 
    ORDER BY name'
);

//funkcje xajax
function select_producer($id)
{
    $JSResponse = new xajaxResponse();
    $models = LMSDB::getInstance()->GetAll('
        SELECT id, name
        FROM netdevicemodels 
        WHERE netdeviceproducerid = ?
        ORDER BY name', 
        array($id)
    );
    $JSResponse->call('update_models', (array)$models);
    return $JSResponse;
}
function select_model($id){
    $JSResponse = new xajaxResponse();
    $ports= LMSDB::getInstance()->GetAll(
	  'SELECT label, port_type, portcount, connector, continous '.
	  'FROM netdeviceschema '.
	  'WHERE model='.$id);
    $JSResponse->call('update_ports', (array)$ports);
    return $JSResponse;

}




$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");

$LMS->InitXajax();
$LMS->RegisterXajaxFunction('select_producer');
$LMS->RegisterXajaxFunction('select_model');
$SMARTY->assign('xajax', $LMS->RunXajax());


$SMARTY->assign('NNnodes',$netnodes);
$SMARTY->assign('producers', $producers);


$SMARTY->display("netelements/netelementadd.html");
?>
