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

switch ($_GET['action']){
    case 'add':
      if(isset($_GET['save']) /*&& validacja*/){
	$elem_id = $LMS->netElementsManager->netElementAdd($_GET['netelem']);
	if(!isset($_GET['netelem[reuse]'])) header('Location: ?m=netelement&action=info&id='.$elem_id);
      }
      $layout['pagetitle'] = trans('New Device');
      $SMARTY->display("netelements/netelementadd.html");
    break;

    case 'edit':
      $layout['pagetitle'] = trans('Device Edit: <b>$a</b> $b $c', $netdevinfo['name'], $netdevinfo['producer'], $netdevinfo['model']);;
      if(!isset($_GET['id']) || !isset($_GET['type'])) header('Location: ?m=netelement');
//	$listdata = $LMS->netElementsManager->netElementsInfo($_GET['id'], $_GET['type'])      
      //dodac asygnacje zmiennych
      $SMARTY->display("netelements/netelementadd.html");
    break;

    case 'info':
      if(!isset($_GET['id']) || !isset($_GET['type'])) header('Location: ?m=netelement');
      $layout['pagetitle'] = trans('Device Info: <b>$a</b> $b $c', $netdevinfo['name'], $netdevinfo['producer'], $netdevinfo['model']);;
//	$listdata = $LMS->netElementsManager->netElementsInfo($_GET['id'], $_GET['type'])      
      //dodac asygnacje zmiennych
      $SMARTY->display("netelements/netelementinfo.html");
      break;

      default:
    //lista urzadzen
      $layout['pagetitle'] = trans('Network elements list');
//cos w ten desen ??
//	$listdata = $LMS->netElementsManager->netElementsList($_GET['o'], $_GET['filter'])      
      //dodac asygnacje zmiennych
//	$SMARTY->assign('listdata,$listdata');      
      $SMARTY->display("netelements/netelemlist.html");
    break;
    }


?>
