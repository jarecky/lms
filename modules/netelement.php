<?php

//funkcje wlasne
$producers = $DB->GetAll('
    SELECT id, name
    FROM netdeviceproducers 
    ORDER BY name'
);

//funkcje xajax
function select_producer($id, $type)
{
    $JSResponse = new xajaxResponse();
    $models = LMSDB::getInstance()->GetAll('
        SELECT id, name
        FROM netdevicemodels 
        WHERE netdeviceproducerid = ? AND type=?
        ORDER BY name', 
        array($id, $type)
    );
    $JSResponse->call('update_models', (array)$models);
    return $JSResponse;
}
function select_model($id, $type){
    $JSResponse = new xajaxResponse();
    $ports= LMSDB::getInstance()->GetAll(
	  'SELECT label, port_type, portcount, connector, continous '.
	  'FROM netdeviceschema '.
	  'WHERE model='.$id);
    $JSResponse->call('update_ports', (array)$ports);
    return $JSResponse;

}

$LMS->InitXajax();
$LMS->RegisterXajaxFunction('select_producer');
$LMS->RegisterXajaxFunction('select_model');
$SMARTY->assign('xajax', $LMS->RunXajax());


$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");


$SMARTY->assign('NNnodes',$netnodes);
$SMARTY->assign('producers', $producers);
if(!isset($_GET['action'])) $_GET['action']='';
print_r($_GET['action']);
  switch ($_GET['action']){
    case 'add':
      $layout['pagetitle'] = trans('New Device');
      $SMARTY->display("netelements/netelementadd.html");
    break;
    case 'edit':
      $layout['pagetitle'] = trans('Device Edit: <b>$a</b> $b $c', $netdevinfo['name'], $netdevinfo['producer'], $netdevinfo['model']);;
      if(!isset($_GET['id']) || !isset($_GET['type'])) header('Location: ?m=netelement');
      //dodac asygnacje zmiennych
      $SMARTY->display("netelements/netelementadd.html");
    break;
    case 'info':
      if(!isset($_GET['id']) || !isset($_GET['type'])) header('Location: ?m=netelement');
      $layout['pagetitle'] = trans('Device Info: <b>$a</b> $b $c', $netdevinfo['name'], $netdevinfo['producer'], $netdevinfo['model']);;
      //dodac asygnacje zmiennych
      $SMARTY->display("netelements/netelementinfo.html");
      break;
    default:
    //lista urzadzen
      $layout['pagetitle'] = trans('Elements list');
      //dodac asygnacje zmiennych
      $SMARTY->display("netelements/netelementlist.html");
    break;
    }


?>
