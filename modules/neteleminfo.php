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

if (!$LMS->NetElemExists($_GET['id'])) {
	$SESSION->redirect('?m=netelemlist');
}

include(MODULES_DIR . '/netelemxajax.inc.php');

if (! array_key_exists('xjxfun', $_POST)) {                  // xajax was called and handled by netelemxajax.inc.php
	$neteleminfo = $LMS->GetNetElem($_GET['id']);
	$netelemconnected = $LMS->GetNetElemConnectedNames($_GET['id']);
	$netcomplist = $LMS->GetNetElemLinkedNodes($_GET['id']);
	$netelemlist = $LMS->GetNotConnectedElements($_GET['id']);

	$nodelist = $LMS->GetUnlinkedNodes();
	$netelemips = $LMS->GetNetElemIPs($_GET['id']);

	$SESSION->save('backto', $_SERVER['QUERY_STRING']);

	$layout['pagetitle'] = trans('Element Info: $a $b $c', $neteleminfo['name'], $neteleminfo['producer'], $neteleminfo['model']);

	$neteleminfo['id'] = $_GET['id'];

	if ($neteleminfo['netnodeid']) {
		$netnode = $DB->GetRow("SELECT * FROM netnodes WHERE id=".$neteleminfo['netnodeid']);
		if ($netnode) {
			$neteleminfo['nodename'] = $netnode['name'];
		}
	}

	$neteleminfo['projectname'] = trans('none');
	if ($neteleminfo['invprojectid']) {
		$prj = $DB->GetRow("SELECT * FROM invprojects WHERE id = ?", array($neteleminfo['invprojectid']));
		if ($prj) {
			if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
				/* inherited */
				if ($netnode) {
					$prj = $DB->GetRow("SELECT * FROM invprojects WHERE id=?",
						array($netnode['invprojectid']));
					if ($prj)
						$neteleminfo['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
				}
			} else
				$neteleminfo['projectname'] = $prj['name'];
		}
	}
	$SMARTY->assign('neteleminfo', $neteleminfo);
	$SMARTY->assign('objectid', $neteleminfo['id']);
	$SMARTY->assign('restnetelemlist', $netelemlist);
	$SMARTY->assign('netelemips', $netelemips);
	$SMARTY->assign('nodelist', $nodelist);
	$SMARTY->assign('elemlinktype', $SESSION->get('elemlinktype'));
	$SMARTY->assign('elemlinktechnology', $SESSION->get('elemlinktechnology'));
	$SMARTY->assign('elemlinkspeed', $SESSION->get('elemlinkspeed'));
	$SMARTY->assign('nodelinktype', $SESSION->get('nodelinktype'));
	$SMARTY->assign('nodelinktechnology', $SESSION->get('nodelinktechnology'));
	$SMARTY->assign('nodelinkspeed', $SESSION->get('nodelinkspeed'));

	$hook_data = $LMS->executeHook('neteleminfo_before_display',
		array(
			'netelemconnected' => $netelemconnected,
			'netcomplist' => $netcomplist,
			'smarty' => $SMARTY,
		)
	);
	$netelemconnected = $hook_data['netelemconnected'];
	$netcomplist = $hook_data['netcomplist'];
	$SMARTY->assign('netelemlist', $netelemconnected);
	$SMARTY->assign('netcomplist', $netcomplist);

	if ($neteleminfo['type']==0) {
		if (isset($_GET['ip'])) {
			$nodeipdata = $LMS->GetNodeConnType($_GET['ip']);
			$netelemauthtype = array();
			$authtype = $nodeipdata;
			if ($authtype != 0) {
				$netelemauthtype['dhcp'] = ($authtype & 2);
				$netelemauthtype['eap'] = ($authtype & 4);
			}
			$SMARTY->assign('nodeipdata', $LMS->GetNode($_GET['ip']));
			$SMARTY->assign('netelemauthtype', $netelemauthtype);
			$SMARTY->display('netelements/netelemipinfo.html');
		} else {
			$SMARTY->display('netelements/neteleminfo.html');
		}
	}
}
?>
