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

function changeType($type,$typelist) {
	global $DB;
	$res = new xajaxResponse();
	$typelist=trim($typelist,',');
	$types=preg_split('/,/',$typelist);
	foreach ($types AS $id) {
		if ($id==$type) {
			$res->assign('level1_'.$id,'style.display', 'table-row');
		} else {
			$res->assign('level1_'.$id,'style.display', 'none');
		}
	}
	$res->call('resize_frame');
	return $res;
}

function changeNetElem($elem,$elemlist) {
	global $DB;
	$res = new xajaxResponse();
	$elemlist=trim($elemlist,',');
	$elems=preg_split('/,/',$elemlist);
	foreach ($elems AS $id) {
		if ($id==$elem) {
			$res->assign('level2_'.$id,'style.display', 'table-row');
		} else {
			$res->assign('level2_'.$id,'style.display', 'none');
		}
	}
	if ($elem>0) {
		$type=$DB->GetOne("SELECT type FROM netelements WHERE id=?",array($elem));
		if ($type==2) {
			$res->assign('level3','style.display', 'table-row');
		} else {
			$res->assign('level3','style.display', 'none');
		}
		$res->assign('level4','style.display', 'table-row');
		$res->assign('level5','style.display', 'table-row');
		$res->assign('level6','style.display', 'table-row');
	} else {
		$res->assign('level3','style.display', 'none');
		$res->assign('level4','style.display', 'none');
		$res->assign('level5','style.display', 'none');
		$res->assign('level6','style.display', 'none');
	}
	$res->call('resize_frame');
	return $res;
}

function connectFiber($params) {
	global $DB;
	$res = new xajaxResponse();
	#$res->alert(print_r($data));
	$result=$DB->Execute("INSERT INTO netconnections (wires,ports,parameter,description) VALUES (?,?,?,?)",array_values($params));	
	if ($result) {
		$content1='';
		$content2='';
		// zamkniecie okna i zapisanie do przeglądarki
		// docelowo - zamiast reloadu - aktualizacja TD
		$res->call('update_fiber_info', $content1, $content2);
	} else {
		#$res->alert(print_r($DB->GetErrors(),true));
	}
	return($res);
}

function validateConnectFiber($data) {
	$res = new xajaxResponse();
	$error=0;
	$params=array();
	$type=$data['type'];
	$element=$data['netelement_'.$type];
	if ($type==2) {
		$wire=$data['wire_'.$element];
		if ($wire<0) {
			$error=1;
			$res->assign('wire_'.$element,'className', 'bold alert');
		} else {
			$res->assign('wire_'.$element,'className', '');
			$params['wires']=$data['wireid'].":".$wire;
		}
		$tray=$data['tray'];
		if ($tray<0) {
			$error=1;
			$res->assign('tray','className', 'bold alert');
		} else {
			$res->assign('tray','className', '');
			$params['ports']=$tray;
		}
	} else {
		$port=$data['port_'.$element];
		if ($port<0) {
			$error=1;
			$res->assign('port_'.$element,'className', 'bold alert');
		} else {
			$res->assign('port_'.$element,'className', '');
			$params['wires']=$data['wireid'];
			$params['ports']=$port;
		}
	}
	if ($data['parameter']<>'' AND !preg_match('/^[0-9]+,?[0-9]*$/',$data['parameter'])) {
		$error=1;
		$res->assign('parameter','className', 'bold alert');
	} else {
		$res->assign('parameter','className', '');
		$params['parameter']=$data['parameter'];
	}
	$params['description']=$data['description'];
	if (!$error) {
		$res->assign('validated','value',1);
		$res->call('xajax_connectFiber',$params);
	} else {
		$res->assign('validated','value',0);
	}

	return($res);
}

global $LMS,$SMARTY;
$LMS->InitXajax();
$LMS->RegisterXajaxFunction(array(
	'changeType','changeNetElem','connectFiber','validateConnectFiber',
));
$SMARTY->assign('xajax', $LMS->RunXajax());

?>
