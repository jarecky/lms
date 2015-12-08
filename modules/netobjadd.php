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

$layout['pagetitle'] = trans('New object');

$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");
$SMARTY->assign('NNnodes',$netnodes);

if (isset($_POST['netobj'])) {
	$netobjdata = $_POST['netobj'];

	if ($netobjdata['name'] == '')
		$error['name'] = trans('Device name is required!');
	elseif (strlen($netobjdata['name'])>32)
		$error['name'] = trans('Device name is too long (max.32 characters)!');

	$netobjdata['purchasetime'] = 0;
	if ($netobjdata['purchasedate'] != '') {
		// date format 'yyyy/mm/dd'
		if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $netobjdata['purchasedate'])) {
			$error['purchasedate'] = trans('Invalid date format!');
		} else {
			$date = explode('/', $netobjdata['purchasedate']);
			if (checkdate($date[1], $date[2], (int)$date[0])) {
				$tmpdate = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
				if (mktime(0,0,0) < $tmpdate)
					$error['purchasedate'] = trans('Date from the future not allowed!');
				else
					$netobjdata['purchasetime'] = $tmpdate;
			} else
				$error['purchasedate'] = trans('Invalid date format!');
		}
	}

	if ($netobjdata['guaranteeperiod'] != 0 && $netobjdata['purchasetime'] == NULL) {
		$error['purchasedate'] = trans('Purchase date cannot be empty when guarantee period is set!');
	}

	if ($netobjdata['invprojectid'] == '-1') { // nowy projekt
		if (!strlen(trim($netobjdata['projectname']))) {
			$error['projectname'] = trans('Project name is required');
		}
		$l = $DB->GetOne("SELECT * FROM invprojects WHERE name=? AND type<>?",
			array($netobjdata['projectname'], INV_PROJECT_SYSTEM));
		if (sizeof($l)>0) {
			$error['projectname'] = trans('Project with that name already exists');
		}
	}
	
	if (!$error) {
		if ($netobjdata['guaranteeperiod'] == -1)
			$netobjdata['guaranteeperiod'] = NULL;

		if (!isset($netobjdata['shortname'])) $netobjdata['shortname'] = '';
		if (!isset($netobjdata['secret'])) $netobjdata['secret'] = '';
		if (!isset($netobjdata['community'])) $netobjdata['community'] = '';
		if (!isset($netobjdata['nastype'])) $netobjdata['nastype'] = 0;

		if (empty($netobjdata['teryt'])) {
			$netobjdata['location_city'] = null;
			$netobjdata['location_street'] = null;
			$netobjdata['location_house'] = null;
			$netobjdata['location_flat'] = null;
		}
		$ipi = $netobjdata['invprojectid'];
		if ($ipi == '-1') {
			$DB->BeginTrans();
			$DB->Execute("INSERT INTO invprojects (name, type) VALUES (?, ?)",
				array($netobjdata['projectname'], INV_PROJECT_REGULAR));
			$ipi = $DB->GetLastInsertID('invprojects');
			$DB->CommitTrans();
		}
		if ($netobjdata['invprojectid'] == '-1' || intval($ipi)>0)
			$netobjdata['invprojectid'] = intval($ipi);
		else
			$netobjdata['invprojectid'] = NULL;
		if ($netobjdata['netnodeid']=="-1") {
			$netobjdata['netnodeid']=NULL;
		} else {
			/* dziedziczenie lokalizacji */
			$dev = $DB->GetRow("SELECT * FROM netnodes WHERE id = ?", array($netobjdata['netnodeid']));
			if ($dev) {
				if (!strlen($netobjdata['location'])) {
					$netobjdata['location'] = $dev['location'];
					$netobjdata['location_city'] = $dev['location_city'];
					$netobjdata['location_street'] = $dev['location_street'];
					$netobjdata['location_house'] = $dev['location_house'];
					$netobjdata['location_flat'] = $dev['location_flat'];
				}
				if (!strlen($netobjdata['longitude']) || !strlen($netobjdata['longitude'])) {
					$netobjdata['longitude'] = $dev['longitude'];
					$netobjdata['latitude'] = $dev['latitude'];
				}
			}
		}

		$netobjid = $LMS->NetObjAdd($netobjdata);

		$SESSION->redirect('?m=netobjinfo&id='.$netobjid);
	}


	$SMARTY->assign('error', $error);
	$SMARTY->assign('netobj', $netobjdata);

}


$SMARTY->display('netobj/netobjadd.html');

?>
