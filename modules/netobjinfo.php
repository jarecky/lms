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

if (!$LMS->NetObjExists($_GET['id'])) {
	$SESSION->redirect('?m=netobjlist');
}


$netobjinfo = $LMS->GetNetObj($_GET['id']);
$cablelist = $LMS->GetNetCabInObj($_GET['id']);
foreach ($cablelist AS $id => $cable)
	$cablelist[$id]['destobj']=$LMS->GetOtherEnd($cable['id'],$_GET['id']);

$othercablelist = $LMS->GetNetCabUnconnected($_GET['id']);
#echo '<PRE>$othercablelist:';print_r($othercablelist);echo '</PRE>';
if ($netobjinfo['type']==1 or $netobjinfo['type']==2) {
	$splicelist = $LMS->GetNetObjSplices($_GET['id']);
	$SMARTY->assign('splicelist', $splicelist);
} elseif ($netobjinfo['type']==3) {
	$in=$DB->GetAll("SELECT * FROM netsplices WHERE dstcableid IS NULL AND objectid=? ORDER BY srccableid ASC,srctube ASC,srcfiber ASC",array($netobjinfo['id']));
	$SMARTY->assign('in',$in);
	$out=$DB->GetAll("SELECT * FROM netsplices WHERE srccableid IS NULL AND objectid=? ORDER BY dstcableid ASC,dsttube ASC,dstfiber ASC",array($netobjinfo['id']));
	$SMARTY->assign('out',$out);
	$data=preg_split('/:/',$netobjinfo['parameter']);
	if (count($in)<$data[0]) $SMARTY->assign('splitter_in',1);
	else $SMARTY->assign('splitter_in',0);
        if (count($out)<$data[1]) $SMARTY->assign('splitter_out',1);
        else $SMARTY->assign('splitter_out',0);
}

$SESSION->save('backto', $_SERVER['QUERY_STRING']);

$layout['pagetitle'] = trans('Object Info: $a $b $c', $netobjinfo['name'], $netobjinfo['producer'], $netobjinfo['model']);

$netobjinfo['id'] = $_GET['id'];

if ($netobjinfo['netnodeid']) {
	$netnode = $DB->GetRow("SELECT * FROM netnodes WHERE id=".$netobjinfo['netnodeid']);
	if ($netnode) {
		$netobjinfo['nodename'] = $netnode['name'];
	}
}

$netobjinfo['projectname'] = trans('none');
if ($netobjinfo['invprojectid']) {
	$prj = $DB->GetRow("SELECT * FROM invprojects WHERE id = ?", array($netobjinfo['invprojectid']));
	if ($prj) {
		if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
			/* inherited */
			if ($netnode) {
				$prj = $DB->GetRow("SELECT * FROM invprojects WHERE id=?",
					array($netnode['invprojectid']));
				if ($prj)
					$netobjinfo['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
			}
		} else
			$netobjinfo['projectname'] = $prj['name'];
	}
}
$SMARTY->assign('netobjinfo', $netobjinfo);
$SMARTY->assign('objectid', $netobjinfo['id']);
$SMARTY->assign('cablelist', $cablelist);
$SMARTY->assign('othercablelist', $othercablelist);

#$hook_data = $LMS->executeHook('netobjinfo_before_display',
#	array(
#		'netobjconnected' => $netobjconnected,
#		'smarty' => $SMARTY,
#	)
#);
#$netobjconnected = $hook_data['netobjconnected'];
#$netcomplist = $hook_data['netcomplist'];
#$SMARTY->assign('netobjlist', $netobjconnected);


$SMARTY->display('netobj/netobjinfo.html');

?>
