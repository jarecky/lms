<?php

function cancel_producer() {
	$obj = new xajaxResponse();

	$obj->assign("id_producer","value","");
	$obj->assign("id_producername","value","");
	$obj->assign("id_alternative_name","value","");
	$obj->script("removeClass(xajax.$('id_producername'),'alert');");
	$obj->script("xajax.$('div_produceredit').style.display='none';");

	return $obj;
}


function add_producer() {
	$obj = new xajaxResponse();

	$obj->script("xajax.$('div_produceredit').style.display='';");
	$obj->script("removeClass(xajax.$('id_producername'),'alert');");
	$obj->assign("id_action_name","innerHTML",trans('New producer'));
	$obj->assign("id_producer","value","");
	$obj->assign("id_producername","value","");
	$obj->assign("id_alternative_name","value","");
	$obj->script("xajax.$('id_producername').focus();");

	return $obj;
}

function edit_producer($id) {
	global $DB;
	$obj = new xajaxResponse();

	$producer = $DB->GetRow('SELECT * FROM netdeviceproducers WHERE id = ?',
		array($id));

	$obj->script("xajax.$('div_produceredit').style.display='';");
	$obj->script("removeClass(xajax.$('id_producername'),'alert');");
	$obj->assign("id_action_name","innerHTML", trans("Producer edit: $a", $producer['name']));

	$obj->assign("id_producer","value", $producer['id']);
	$obj->assign("id_producername","value", $producer['name']);
	$obj->assign("id_alternative_name","value", $producer['alternative_name']);
	$obj->script("xajax.$('id_producername').focus();");

	return $obj;
}

function save_producer($forms) {
	global $DB;
	$obj = new xajaxResponse();

	$form = $forms['produceredit'];
	$formid = $form['id'];
	$pid = $form['pid'];
	$error = false;

	$obj->script("removeClass(xajax.$('id_producername'),'alert');");

	if (empty($form['name'])) {
		$error = true;
		$obj->setEvent("id_producername","onmouseover", "popup('<span class=\\\"red bold\\\">" . trans("Producer name is required!") . "</span>')");
	}

	if (!$error) {
		if (!$form['id'])
			$error = ($DB->GetOne('SELECT COUNT(*) FROM netdeviceproducers WHERE name = ?',
				array(strtoupper($form['name']))) ? true : false);
		else
			$error = ($DB->GetOne('SELECT COUNT(*) FROM netdeviceproducers WHERE name = ? AND id <> ? ',
				array(strtoupper($form['name']), $form['id'])) ? true : false);

		if ($error)
			$obj->setEvent("id_producername","onmouseover", "popup('<span class=\\\"red bold\\\">" . trans("Producer already exists!") . "</span>')");
	}

	if ($error) {
		$obj->script("addClass(xajax.$('id_producername'),'alert');");
		$obj->script("xajax.$('id_producername').focus();");
	} else {
		if ($form['id']) {
			$DB->Execute('UPDATE netdeviceproducers SET name = ?, alternative_name = ? WHERE id = ?',
				array($form['name'],
					($form['alternative_name'] ? $form['alternative_name'] : NULL),
					$form['id']
				));
			$obj->script("xajax_cancel_producer();");
			$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=$formid';");
		} else {
			$DB->Execute('INSERT INTO netdeviceproducers (name, alternative_name) VALUES (?, ?)',
				array($form['name'],
					($form['alternative_name'] ? $form['alternative_name'] : NULL)
				));

			$obj->script("xajax_cancel_producer();");
			$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=" . $DB->getLastInsertId('netdeviceproducers') . "';");
		}
	}

	return $obj;
}

function delete_producer($id) {
	global $DB;
	$obj = new xajaxResponse();

	$DB->Execute('DELETE FROM netdeviceproducers WHERE id = ?', array($id));

	$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=';");

	return $obj;
}


function cancel_model() {
	$obj = new xajaxResponse();

	$obj->assign("id_model","value","");
	$obj->assign("id_modelname","value","");
	$obj->assign("id_model_alternative_name","value","");
	$obj->script("removeClass(xajax.$('id_model_name'),'alert');");
	$obj->script("xajax.$('div_modeledit').style.display='none';");

	return $obj;
}

function add_model() {
	$obj = new xajaxResponse();

	$obj->script("xajax.$('div_modeledit').style.display='';");
	$obj->script("removeClass(xajax.$('id_model_name'),'alert');");
	$obj->assign("id_model_action_name","innerHTML",trans("New model"));
	$obj->assign("id_model","value","");
	$obj->assign("id_model_name","value","");
	$obj->assign("porttable","innerHTML","");
	$obj->assign("id_model_alternative_name","value","");
	$obj->script("xajax.$('id_model_name').focus();");

	return $obj;
}
function addports($devtype){
	global $NETPORTTYPES, $NETCONNECTORS, $NETTECHNOLOGIES;
	$res = new xajaxResponse();
	$index=substr(microtime(),2,8);
  switch($devtype){
    case 0://active
    case 99: //client computer
  	$types_allowed=array(1,2,3,4,100,200);
	$connectors_allowed=array(1,2,3,4,5,6,50,51, 100,101,102,103,104,151, 201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	$tech_allowed=array(1,2,3,4,5,6,7,8,9,10,11,12,50,51,52,70,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114, 200,201,202,203,204,205,206,207,208,209,210,211,212,213);
    break;
    case 1://passive
	$types_allowed=array(1,2,200);
  	$connectors_allowed=array(1,2,3,4,5,6,50,51, 201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
  	$tech_allowed=array('null');
    break;
  }
	foreach($types_allowed as $t){
		$toptions.='<option value="'.$t.'">'.$NETPORTTYPES[$t].'</option>';		
	}
	foreach($connectors_allowed as $c){
		$coptions.='<option value="'.$c.'">'.$NETCONNECTORS[$c].'</option>';
	}
	foreach($tech_allowed as $tn){
		if($tn=='null')$techoptions.='<option value="null">N/A</option>';
		else $techoptions.='<option value="'.$tn.'">'.$NETTECHNOLOGIES[$tn]['name'].'</option>';
	}
	$res->append('porttable','innerHTML','<tr><td class="nobr" colspan="3">'.trans('Label:').'<input name="netports['.$index.'][label]">
		      <select name="netports['.$index.'][netporttype]" id="ptype'.$index.'" onchange="xajax_updateTechnologyAndConnector(\''.$index.'\', document.getElementById(\'devtype\').value, this.value)">'.$toptions.'</select>
		      '.trans("Technology").':<select name="netports['.$index.'][nettechnology]" id="ptech'.$index.'">'.$techoptions.'</select>
		      '.trans("Connector").':<select name="netports['.$index.'][netconnector]" id="pconn'.$index.'">'.$coptions.'</select>
		      <IMG src="img/add.gif" alt="" title="{trans("Clone")}" onclick="clone(this);">&nbsp;
		      <IMG src="img/delete.gif" alt="" title="'.trans("Delete").'" onclick="remports(this);">
		      </td></tr>');

	
	
	return $res;
}

function edit_model($id, $dev_type=0) {
error_log('i:'.$id.' med:'.$dev_type);
	global $DB, $NETPORTTYPES, $NETCONNECTORS, $NETELEMENTTYPES, $NETTECHNOLOGIES;
	$obj = new xajaxResponse();

	$model = $DB->GetRow('SELECT * FROM netdevicemodels WHERE id = ?', array($id));
	$obj->assign('porttable','innerHTML','');
	switch($dev_type){
    case 0://active
    case 99: //client computer
  	$types_allowed=array(1,2,3,4,100,200);
	$connectors_allowed=array(1,2,3,4,5,6,50,51, 100,101,102,103,104,151, 201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	$tech_allowed=array(1,2,3,4,5,6,7,8,9,10,11,12,50,51,52,70,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114, 200,201,202,203,204,205,206,207,208,209,210,211,212,213);
    break;
    case 1://passive
	$types_allowed=array(1,2,200);
  	$connectors_allowed=array(1,2,3,4,5,6,50,51, 201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
  	$tech_allowed=array('null');
    break;
    default:
	$types_allowed=array();
  	$connectors_allowed=array();
  	$tech_allowed=array('null');
  }
	$ports = $DB->getAll("SELECT id, label, connector, port_type, technology FROM netdeviceschema WHERE model=".$id." ORDER by port_type, label");

//	  $obj->call('xaddport',$p['id'],$p['label'],$p['connector'],$p['port_type'], $NETCONNECTORS[($p['connector'])], $NETPORTTYPES[($p['port_type'])]);
	foreach($ports as $p){
	  $toptions='';
	  $techoptions='';
	  $coptions='';
	  foreach($types_allowed as $t){
		if( $t==$p['port_type']) $tsel='selected';
		$toptions.='<option value="'.$t.'" '.$tsel.'>'.$NETPORTTYPES[$t].'</option>';
		$tsel='';
	  }
	  foreach($tech_allowed as $tn){
		if( $tn==$p['technology']) $tesel='selected';
		if($tn=='null')$techoptions.='<option value="null">N/A</option>';
		else $techoptions.='<option value="'.$tn.'" '.$tesel.'>'.$NETTECHNOLOGIES[$tn]['name'].'</option>';
		$tesel='';
	  }
	  foreach($connectors_allowed as $c){
		if( $c==$p['connector']) $csel='selected';
		$coptions.='<option value="'.$c.'" '.$tsel.'>'.$NETCONNECTORS[$c].'</option>';
		$csel='';
	  }
	
	  $list='<tr>
			  <td class="nobr" colspan="3">
			    '.trans("Label:").'<input type=text name="netports['.$p['id'].'][label]" value="'.$p['label'].'">
			    '.trans("Type:").'<select name="netports['.$p['id'].'][netporttype]" id="ptype'.$p['id'].'" onchange="xajax_updateTechnologyAndConnector(\''.$p['id'].'\', document.getElementById(\'devtype\').value, this.value)">';		    
	$list.=$toptions;
	$list.='</select>'.trans("Technology")
		  .': <select name="netports['.$p['id'].'][nettechnology]" id="ptech'.$p['id'].'" onchange="xajax_getConnectorOptionsByMediumAndDevType(this.value, document.getElementById(\'medium'.$p['id'].'\').value, \'pconn'.$p['id'].'\')">';
	$list.=$techoptions.'</select>';
		  $list.=trans("Connector").':<select name="netports['.$p['id'].'][netconnector]" id="pconn'.$p['id'].'">';

	$list.=$coptions.'</select>';
	$list.='<IMG src="img/clone.gif" alt="" title="{trans("Clone")}" onclick="clone(this);">&nbsp;
			    <IMG src="img/delete.gif" alt="" title="{trans("Delete")}" onclick="remports(this);">
			  </td>
		</tr>';
			
	$obj->append('porttable','innerHTML',$list);
	}
	$devtypes='';
	foreach($NETELEMENTTYPES as $k=>$v){ 
	    if($k==$dev_type) $asel='selected';
	    $devtypes.='<option value="'.$k.'" '.$asel.'>'.$v.'</option>';
	    $asel='';
	}
	$obj->assign('devtype', 'innerHTML', $devtypes);
	$obj->script("xajax.$('div_modeledit').style.display='';");
	$obj->script("removeClass(xajax.$('id_model_name'),'alert');");
	$obj->assign("id_model_action_name","innerHTML", trans('Model edit: $a', $model['name']));

	$obj->assign("id_model","value", $model['id']);
	$obj->assign("id_model_name","value", $model['name']);
	$obj->assign("id_model_alternative_name","value", $model['alternative_name']);
	$obj->script("xajax.$('id_model_name').focus();");

	return $obj;
}

function updateTechnologyAndConnector($index, $devtype, $medium){
	global $NETTECHNOLOGIES, $NETCONNECTORS;
	$res= new xajaxResponse();
error_log('i:'.$index.' typ:'.$devtype.' med:'.$medium);
  switch($devtype){
    case 0://active
    case 99: //client computer
	switch($medium){
	    case 1:
	      $connectors_allowed=array(1,2,3,4,5,6,50,51);
	      $tech_allowed=array(1,2,3,4,5,6,7,8,9,10,11,12,50,51,52,70);
	    break;
	    case 2:
	      $connectors_allowed=array(3,6);
	      $tech_allowed=array(1,2,3,4,5,10,11,12,70);
	    break;
	    case 3:
	    case 4:
	      $connectors_allowed=array(1,2,200,201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	      $tech_allowed=array(6,7,8,9,10,200,201,202,203,204,205,206,207,208,209,210,211,212,213);
	    break;
	    case 100:
	      $connectors_allowed=array(100,101,102,103,104,151);
	      $tech_allowed=array(100,101,102,103,104,105,106,107,108,109,110,111,112,113,114);
	    break;
	    case 200:
	      $connectors_allowed=array(200,201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	      $tech_allowed=array(200,201,202,203,204,205,206,207,208,209,210,211,212,213);
	    break;
	}
    break;
    case 1://passive
  	switch($medium){
	    case 1:
	      $connectors_allowed=array(1,2,3,4,5,6,50,51);
	      $tech_allowed=array('null');
	    break;
	    case 2:
	      $connectors_allowed=array(3,6);
	      $tech_allowed=array('null');
	    break;
	    case 3:
	    case 4:
	      $connectors_allowed=array(1,2,200,201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	      $tech_allowed=array('null');
	    break;
	    case 200:
	      $connectors_allowed=array(200,201,202,203,210,211,212,213,220,221,222,223,230,231,232,233,240,241,242,243);
	      $tech_allowed=array('null');
	    break;
	}
    break;
  }
	foreach($connectors_allowed as $c){
		$coptions.='<option value="'.$c.'">'.$NETCONNECTORS[$c].'</option>';
	}
	foreach($tech_allowed as $tn){
		if($tn=='null')$techoptions.='<option value="null">N/A</option>';
		else $techoptions.='<option value="'.$tn.'">'.$NETTECHNOLOGIES[$tn]['name'].'</option>';
	}
	$res->assign('ptech'.$index, 'innerHTML', $techoptions);
	$res->assign('pconn'.$index, 'innerHTML', $coptions);
	return $res;
}

function save_model($forms) {
	 global $DB;
	$obj = new xajaxResponse();

	$form = $forms['modeledit'];
	$formid = intval($form['id']);
	$pid = intval($form['pid']);
	$error = false;

	$obj->script("removeClass(xajax.$('id_model_name'),'alert');");

	if (empty($form['name'])) {
		$error = true;
		$obj->setEvent("id_model_name","onmouseover", "popup('<span class=\\\"red bold\\\">" . trans("Model name is required!") . "</span>')");
	}

	if (!$error) {
		if (!$form['id'])
			$error = ($DB->GetOne('SELECT COUNT(*) FROM netdevicemodels WHERE netdeviceproducerid = ? AND UPPER(name) = ? ',
				array($pid, strtoupper($form['name']))) ? true : false);
		else
			$error = ($DB->GetOne('SELECT COUNT(*) FROM netdevicemodels WHERE id <> ? AND netdeviceproducerid = ? AND UPPER(name) = ?',
				array($formid, $pid, strtoupper($form['name']))) ? true : false);

		if ($error)
			$obj->setEvent("id_model_name","onmouseover", "popup('<span class=\\\"red bold\\\">" . trans("Model already exists!") . "</span>')");
	}

	if ($error) {
		$obj->script("addClass(xajax.$('id_model_name'),'alert');");
		$obj->script("xajax.$('id_model_name').focus();");
	} else {
		if ($formid) {
			$DB->Execute('UPDATE netdevicemodels SET name = ?, alternative_name = ?, type = ? WHERE id = ?',
				array($form['name'],
					($form['alternative_name'] ? $form['alternative_name'] : NULL),
					$form['type'],
					$formid,
				));
			$vals='';
			foreach($forms['netports'] as $key=>$val){
			  if(preg_match("/\d+/",$key)) $vals.="('".$form['id']."','".$forms['netports']["$key"]['label']."','".$forms['netports']["$key"]['netporttype']."','".$forms['netports']["$key"]['netconnector']."','".$forms['netports']["$key"]['nettechnology']."'),";
			}
			$DB->Execute("DELETE FROM netdeviceschema WHERE model=".$formid);
			$q="INSERT INTO netdeviceschema (model,label, port_type, connector, technology) VALUES ".substr($vals,0,-1);
			error_log($q);
			$DB->Execute($q);
			$obj->script("xajax_cancel_model();");
			$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=$pid';");
		} else {
			$DB->BeginTrans();
			$DB->Execute('INSERT INTO netdevicemodels (netdeviceproducerid, name, alternative_name, type) VALUES (?, ?, ?, ?)',
				array($pid,
					$form['name'],
					($form['alternative_name'] ? $form['alternative_name'] : NULL),
					$form['type'],
				));
			$id=$DB->GetLastInsertId();
			  foreach($forms['netports'] as $key=>$val){
			    if(preg_match("/\d+/",$key)) $vals.="('".$id."','".$forms['netports']["$key"]['label']."','".$forms['netports']["$key"]['netporttype']."','".$forms['netports']["$key"]['netconnector']."','".$forms['netports']["$key"]['nettechnology']."'),";
			  }
			  error_log(print_r($vals));
			$DB->Execute("INSERT INTO netdeviceschema (model,label, port_type, connector, technology) VALUES ".substr($vals,0,-1));
			$DB->CommitTrans();
			$obj->script("xajax_cancel_model();");
			$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=$pid';");
		}
	}

	return $obj;
}

function delete_model($id) {
	global $DB;
	$obj = new xajaxResponse();

	$id = intval($id);

	$pid = $DB->GetOne('SELECT p.id FROM netdevicemodels m
		JOIN netdeviceproducers p ON (p.id = m.netdeviceproducerid) WHERE m.id = ?',
		array($id));

	$DB->Execute('DELETE FROM netdevicemodels WHERE id = ?', array($id));

	$obj->script("self.location.href='?m=netelement&action=models&page=1&p_id=$pid';");

	return $obj;
}

global $LMS,$SMARTY;
$LMS->InitXajax();
$LMS->RegisterXajaxFunction(array(
	'cancel_producer',
	'add_producer',
	'edit_producer',
	'save_producer',
	'delete_producer',
	'cancel_model',
	'add_model',
	'edit_model',
	'save_model',
	'delete_model',
	'updateTechnologyAndConnector',
	'addports',
));

$SMARTY->assign('xajax', $LMS->RunXajax());

?>
