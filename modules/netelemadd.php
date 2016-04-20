<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2016 LMS Developers
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

if(isset($_POST['netelem']))
{
	$netelemdata = $_POST['netelem'];

	if($netelemdata['ports'] == '')
		$netelemdata['ports'] = 0;
	else
		$netelemdata['ports'] = intval($netelemdata['ports']);

	if(empty($netelemdata['clients']))
		$netelemdata['clients'] = 0;
	else
		$netelemdata['clients'] = intval($netelemdata['clients']);

	if($netelemdata['name'] == '')
		$error['name'] = trans('Element name is required!');
	elseif (strlen($netelemdata['name']) > 60)
		$error['name'] = trans('Specified name is too long (max. $a characters)!', '60');

	$netelemdata['purchasetime'] = 0;
	if($netelemdata['purchasedate'] != '') 
	{
		// date format 'yyyy/mm/dd'
		if(!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $netelemdata['purchasedate']))
		{
			$error['purchasedate'] = trans('Invalid date format!');
		}
		else
		{
			$date = explode('/', $netelemdata['purchasedate']);
			if(checkdate($date[1], $date[2], (int)$date[0]))
			{
				$tmpdate = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
                        	if(mktime(0,0,0) < $tmpdate)
			                $error['purchasedate'] = trans('Date from the future not allowed!');
				else
				        $netelemdata['purchasetime'] = $tmpdate;
			}
			else
				$error['purchasedate'] = trans('Invalid date format!');
		}
	}

	if($netelemdata['guaranteeperiod'] != 0 && $netelemdata['purchasetime'] == NULL)
	{
		$error['purchasedate'] = trans('Purchase date cannot be empty when guarantee period is set!');
	}


	if ($netelemdata['invprojectid'] == '-1') { // nowy projekt
		if (!strlen(trim($netelemdata['projectname']))) {
		 $error['projectname'] = trans('Project name is required');
		}
		$l = $DB->GetOne("SELECT * FROM invprojects WHERE name=? AND type<>?",
			array($netelemdata['projectname'], INV_PROJECT_SYSTEM));
		if (sizeof($l)>0) {
			$error['projectname'] = trans('Project with that name already exists');
		}
	}

    if(!$error)
    {
		if($netelemdata['guaranteeperiod'] == -1)
			$netelemdata['guaranteeperiod'] = NULL;

		if(!isset($netelemdata['shortname'])) $netelemdata['shortname'] = '';
        if(!isset($netelemdata['secret'])) $netelemdata['secret'] = '';
        if(!isset($netelemdata['community'])) $netelemdata['community'] = '';
        if(!isset($netelemdata['nastype'])) $netelemdata['nastype'] = 0;

        if (empty($netelemdata['teryt'])) {
            $netelemdata['location_city'] = null;
            $netelemdata['location_street'] = null;
            $netelemdata['location_house'] = null;
            $netelemdata['location_flat'] = null;
	}
	$ipi = $netelemdata['invprojectid'];
	if ($ipi == '-1') {
		$DB->BeginTrans();
		$DB->Execute("INSERT INTO invprojects (name, type) VALUES (?, ?)",
			array($netelemdata['projectname'], INV_PROJECT_REGULAR));
		$ipi = $DB->GetLastInsertID('invprojects');
		$DB->CommitTrans();
	} 
	if ($netelemdata['invprojectid'] == '-1' || intval($ipi)>0)
		$netelemdata['invprojectid'] = intval($ipi);
	else
		$netelemdata['invprojectid'] = NULL;
	if ($netelemdata['netnodeid']=="-1") {
		$netelemdata['netnodeid']=NULL;
	}
	else {
		/* dziedziczenie lokalizacji */
		$dev = $DB->GetRow("SELECT * FROM netnodes WHERE id = ?", array($netelemdata['netnodeid']));
		if ($dev) {
			if (!strlen($netelemdata['location'])) {
				$netelemdata['location'] = $dev['location'];
				$netelemdata['location_city'] = $dev['location_city'];
				$netelemdata['location_street'] = $dev['location_street'];
				$netelemdata['location_house'] = $dev['location_house'];
				$netelemdata['location_flat'] = $dev['location_flat'];
			}
			if (!strlen($netelemdata['longitude']) || !strlen($netelemdata['longitude'])) {
				$netelemdata['longitude'] = $dev['longitude'];
				$netelemdata['latitude'] = $dev['latitude'];
			}
		}
	}

		$netelemid = $LMS->NetElemAdd($netelemdata);

		$SESSION->redirect('?m=neteleminfo&id='.$netelemid);
    }

	$SMARTY->assign('error', $error);
	$SMARTY->assign('netelem', $netelemdata);
} elseif (isset($_GET['id'])) {
	$netelemdata = $LMS->GetNetElem($_GET['id']);
	$netelemdata['name'] = trans('$a (clone)', $netelemdata['name']);
	$netelemdata['teryt'] = !empty($netelemdata['location_city']) && !empty($netelemdata['location_street']);
	$SMARTY->assign('netelem', $netelemdata);
}

$layout['pagetitle'] = trans('New Element');

$SMARTY->assign('nastype', $LMS->GetNAStypes());

$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");
$SMARTY->assign('NNnodes',$netnodes);

if (ConfigHelper::checkConfig('phpui.ewx_support'))
	$SMARTY->assign('channels', $DB->GetAll('SELECT id, name FROM ewx_channels ORDER BY name'));

$SMARTY->display('netelements/netelemadddevice.html');

?>
