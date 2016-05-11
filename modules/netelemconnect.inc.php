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

global $LMS,$SMARTY;
$LMS->InitXajax();
$LMS->RegisterXajaxFunction(array(
	'changeType','changeNetElem','connectFiber',
));
$SMARTY->assign('xajax', $LMS->RunXajax());

?>
