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

$layout['pagetitle'] = trans('New cable');

$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$objects = $LMS->GetNetObjList('name,asc',array());
unset($objects['total']);
unset($objects['order']);
unset($objects['direction']);
$SMARTY->assign('objects',$objects);

if (isset($_POST['netcab'])) {
	$netcabdata = $_POST['netcab'];
	echo '<PRE>';print_r($netcabdata);echo '</PRE>';
	
	if ($netcabdata['name'] == '')
		$error['name'] = trans('Cable name is required!');
	elseif (strlen($netcabdata['name'])>32)
		$error['name'] = trans('Cable name is too long (max.32 characters)!');


	$netcabdata['fibers']=intval($netcabdata['fibers']);
	if ($netcabdata['fibers']==0)
		$error['fibers'] = trans('Musisz podać poprawną ilość włókien!');

	if ($netcabdata['begin']==$netcabdata['end']) {
		$error['begin'] = trans('Koniec połączenia musi być różny niż początek');
		$error['end'] = trans('Koniec połączenia musi być różny niż początek');
	}
	if ($netcabdata['begin']==0) unset($netcabdata['begin']);
	if ($netcabdata['end']==0) unset($netcabdata['end']);

	$netcabdata['purchasetime'] = 0;
	if ($netcabdata['purchasedate'] != '') {
		// date format 'yyyy/mm/dd'
		if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $netcabdata['purchasedate'])) {
			$error['purchasedate'] = trans('Invalid date format!');
		} else {
			$date = explode('/', $netcabdata['purchasedate']);
			if (checkdate($date[1], $date[2], (int)$date[0])) {
				$tmpdate = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
				if (mktime(0,0,0) < $tmpdate)
					$error['purchasedate'] = trans('Date from the future not allowed!');
				else
					$netcabdata['purchasetime'] = $tmpdate;
			} else
				$error['purchasedate'] = trans('Invalid date format!');
		}
	}

	if ($netcabdata['guaranteeperiod'] != 0 && $netcabdata['purchasetime'] == NULL) {
		$error['purchasedate'] = trans('Purchase date cannot be empty when guarantee period is set!');
	}

	if ($netcabdata['invprojectid'] == '-1') { // nowy projekt
		if (!strlen(trim($netcabdata['projectname']))) {
			$error['projectname'] = trans('Project name is required');
		}
		$l = $DB->GetOne("SELECT * FROM invprojects WHERE name=? AND type<>?",
			array($netcabdata['projectname'], INV_PROJECT_SYSTEM));
		if (sizeof($l)>0) {
			$error['projectname'] = trans('Project with that name already exists');
		}
	}
	
	if (!$error) {
		if ($netcabdata['guaranteeperiod'] == -1)
			$netcabdata['guaranteeperiod'] = NULL;

		$ipi = $netcabdata['invprojectid'];
		if ($ipi == '-1') {
			$DB->BeginTrans();
			$DB->Execute("INSERT INTO invprojects (name, type) VALUES (?, ?)",
				array($netcabdata['projectname'], INV_PROJECT_REGULAR));
			$ipi = $DB->GetLastInsertID('invprojects');
			$DB->CommitTrans();
		}
		if ($netcabdata['invprojectid'] == '-1' || intval($ipi)>0)
			$netcabdata['invprojectid'] = intval($ipi);
		else
			$netcabdata['invprojectid'] = NULL;

		echo '<PRE>';print_r($netcabdata);echo '</PRE>';
	
		$netcabid = $LMS->NetCabAdd($netcabdata);
		$SESSION->redirect('?m=netcablist');
	}

	$SMARTY->assign('error', $error);
	$SMARTY->assign('netcab', $netcabdata);

}


$SMARTY->display('netcab/netcabadd.html');

?>
