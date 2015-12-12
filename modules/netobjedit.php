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

$action = !empty($_GET['action']) ? $_GET['action'] : '';
$edit = '';
$subtitle = '';

switch ($action) {
	case 'replace':

		$dev1 = $LMS->GetNetObj($_GET['id']);
		$dev2 = $LMS->GetNetObj($_GET['netobj']);

		if ($dev1['ports'] < $dev2['takenports']) {
			$error['replace'] = trans('It scans for ports in source device!');
		} elseif ($dev2['ports'] < $dev1['takenports']) {
			$error['replace'] = trans('It scans for ports in destination device!');
		}

		if (!$error) {
			$links1 = $DB->GetAll('(SELECT type, 
				(CASE src WHEN ? THEN dst ELSE src END) AS id,
				speed, technology,
				(CASE src WHEN ? THEN srcport ELSE dstport END) AS srcport,
				(CASE src WHEN ? THEN dstport ELSE srcport END) AS dstport,
				(CASE src WHEN ? THEN srcradiosector ELSE dstradiosector END) AS srcradiosector,
				(CASE src WHEN ? THEN dstradiosector ELSE srcradiosector END) AS dstradiosector
				FROM netlinks WHERE src = ? OR dst = ?)
			UNION
				(SELECT linktype AS type, linkradiosector AS srcradiosector, NULL AS dstradiosector,
				linktechnology AS technology, linkspeed AS speed, id, port AS srcport, NULL AS dstport
				FROM nodes WHERE netobj = ? AND ownerid > 0)
			ORDER BY srcport', array($dev1['id'], $dev1['id'], $dev1['id'], $dev1['id'], $dev1['id'],
					$dev1['id'], $dev1['id'], $dev1['id']));

			$links2 = $DB->GetAll('(SELECT type, 
				(CASE src WHEN ? THEN dst ELSE src END) AS id,
				speed, technology,
				(CASE src WHEN ? THEN srcport ELSE dstport END) AS srcport,
				(CASE src WHEN ? THEN dstport ELSE srcport END) AS dstport,
				(CASE src WHEN ? THEN srcradiosector ELSE dstradiosector END) AS srcradiosector,
				(CASE src WHEN ? THEN dstradiosector ELSE srcradiosector END) AS dstradiosector
				FROM netlinks WHERE src = ? OR dst = ?)
			UNION
				(SELECT linktype AS type, linkradiosector AS srcradiosector, NULL AS dstradiosector,
					linktechnology AS technology, linkspeed AS speed, id, port AS srcport, NULL AS dstport
					FROM nodes WHERE netobj = ? AND ownerid > 0)
			ORDER BY srcport', array($dev2['id'], $dev2['id'], $dev2['id'], $dev2['id'], $dev2['id'],
					$dev2['id'], $dev2['id'], $dev2['id']));

			$DB->BeginTrans();

			$DB->Execute('UPDATE netobjices SET location = ?, latitude = ?, longitude = ?
				WHERE id = ?', array($dev1['location'], $dev1['latitude'], $dev1['longitude'], $dev2['id']));
			$DB->Execute('UPDATE netobjices SET location = ?, latitude = ?, longitude = ?
				WHERE id = ?', array($dev2['location'], $dev2['latitude'], $dev2['longitude'], $dev1['id']));

			if ($SYSLOG) {
				$args = array(
					$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $dev2['id'],
					'location' => $dev1['location'],
					'latitude' => $dev1['latitude'],
					'longitude' => $dev1['longitude'],
				);
				$SYSLOG->AddMessage(SYSLOG_RES_NETDEV, SYSLOG_OPER_UPDATE, $args,
					array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV]));
				$args = array(
					$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $dev1['id'],
					'location' => $dev2['location'],
					'latitude' => $dev2['latitude'],
					'longitude' => $dev2['longitude'],
				);
				$SYSLOG->AddMessage(SYSLOG_RES_NETDEV, SYSLOG_OPER_UPDATE, $args,
					array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV]));
			}

			$LMS->NetObjDelLinks($dev1['id']);
			$LMS->NetObjDelLinks($dev2['id']);

			$ports = array();
			// przypisujemy urzadzenia/komputer, probujac zachowac numeracje portow
			if ($links1)
				foreach ($links1 as $row) {
					$sport = $row['srcport'];
					if ($sport) {
						if ($sport > $dev2['ports'])
							for ($i = 1; $i <= $dev2['ports']; $i++)
								if (!isset($ports[$sport])) {
									$sport = $i;
									break;
								}

						$ports[$sport] = $sport;
					}

					if (isset($row['dstport'])) // device
						$LMS->NetObjLink($dev2['id'], $row['id'], array(
							'type' => $row['type'],
							'srcradiosector' => $row['srcradiosector'],
							'dstradiosector' => $row['dstradiosector'],
							'technology' => $row['technology'],
							'speed' => $row['speed'],
							'srcport' => $sport,
							'dstport' => $row['dstport'],
						));
					else // node
						$LMS->NetObjLinkNode($row['id'], $dev2['id'], array(
							'type' => $row['type'],
							'radiosector' => $row['srcradiosector'],
							'technology' => $row['technology'],
							'speed' => $row['speed'],
							'port' => $sport,
						));
				}

			$ports = array();
			if ($links2)
				foreach ($links2 as $row) {
					$sport = $row['srcport'];
					if ($sport) {
						if ($sport > $dev1['ports'])
							for ($i = 1; $i <= $dev1['ports']; $i++)
								if (!isset($ports[$sport])) {
									$sport = $i;
									break;
								}

						$ports[$sport] = $sport;
					}

					if (isset($row['dstport'])) // device
						$LMS->NetObjLink($dev1['id'], $row['id'], array(
							'type' => $row['type'],
							'srcradiosector' => $row['srcradiosector'],
							'dstradiosector' => $row['dstradiosector'],
							'technology' => $row['technology'],
							'speed' => $row['speed'],
							'srcport' => $sport,
							'dstport' => $row['dstport']
						));
					else // node
						$LMS->NetObjLinkNode($row['id'], $dev1['id'], array(
							'type' => $row['type'],
							'radiosector' => $row['srcradiosector'],
							'technology' => $row['technology'],
							'speed' => $row['speed'],
							'port' => $sport,
						));
				}

			$DB->CommitTrans();

			$SESSION->redirect('?m=netobjinfo&id=' . $_GET['id']);
		}

		break;

	case 'disconnect':

		$LMS->NetObjUnLink($_GET['id'], $_GET['devid']);
		$SESSION->redirect('?m=netobjinfo&id=' . $_GET['id']);

	case 'disconnectnode':

		$LMS->NetObjLinkNode($_GET['nodeid'], 0);
		$SESSION->redirect('?m=netobjinfo&id=' . $_GET['id']);

	case 'connect':

		echo '<PRE>';print_r($_GET);echo '</PRE>';
	  	list($srccableid,$srctube,$srcfiber)=preg_split('/,/',$_GET['srccable']);	
	  	list($dstcableid,$dsttube,$dstfiber)=preg_split('/,/',$_GET['dstcable']);	
		if ($srccableid==$dstcableid)
			$error['cable2']=trans('Cannot connect cable with themself!');
		if (!$error) {
			$LMS->NetObjSplice($_GET['id'],$_GET['srccable'], $_GET['dstcable'], $_GET['position'], $_GET['description']);
			$SESSION->redirect('?m=netobjinfo&id=' . $_GET['id']);
		}

		break;

	default:
		$edit = 'data';
		break;
}

if (isset($_POST['netobj'])) {
	$netobjdata = $_POST['netobj'];
	$netobjdata['id'] = $_GET['id'];

	if ($netobjdata['name'] == '')
		$error['name'] = trans('Device name is required!');
	elseif (strlen($netobjdata['name']) > 32)
		$error['name'] = trans('Specified name is too long (max.$a characters)!', '32');

        switch ($netobjdata['type']) {
                case 0:
                        $netobjdata['parameter']=$netobjdata['reserve_quantity'];
                        break;
                case 1:
                        $netobjdata['parameter']=$netobjdata['closure_capacity'];
                        break;
                case 2:
                        $netobjdata['parameter']=$netobjdata['box_adaptors'];
                        break;
                case 3:
                        $netobjdata['parameter']=$netobjdata['splitter_in'].':'.$netobjdata['splitter_out'];
                        break;
                default:
                        $netobjdata['parameter']='';
        }

	#if ($netobjdata['ports'] < $LMS->CountNetObjLinks($_GET['id']))
	#	$error['ports'] = trans('Connected devices number exceeds number of ports!');

	$netobjdata['purchasetime'] = 0;
	if ($netobjdata['purchasedate'] != '') {
		// date format 'yyyy/mm/dd'
		if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $netobjdata['purchasedate'])) {
			$error['purchasedate'] = trans('Invalid date format!');
		} else {
			$date = explode('/', $netobjdata['purchasedate']);
			if (checkdate($date[1], $date[2], (int) $date[0])) {
				$tmpdate = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
				if (mktime(0, 0, 0) < $tmpdate)
					$error['purchasedate'] = trans('Date from the future not allowed!');
				else
					$netobjdata['purchasetime'] = $tmpdate;
			}
			else
				$error['purchasedate'] = trans('Invalid date format!');
		}
	}

	if ($netobjdata['guaranteeperiod'] != 0 && $netobjdata['purchasedate'] == '') {
		$error['purchasedate'] = trans('Purchase date cannot be empty when guarantee period is set!');
	}

	if ($netobjdata['invprojectid'] == '-1') { // nowy projekt
		if (!strlen(trim($netobjdata['projectname']))) {
		 $error['projectname'] = trans('Project name is required');
		}
		if ($DB->GetOne("SELECT id FROM invprojects WHERE name=? AND type<>?",
			array($netobjdata['projectname'], INV_PROJECT_SYSTEM)))
			$error['projectname'] = trans('Project with that name already exists');
	}

	if (!$error) {
		if ($netobjdata['guaranteeperiod'] == -1)
			$netobjdata['guaranteeperiod'] = NULL;

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
		if ($netobjdata['invprojectid'] == '-1' || intval($ipi)>0) {
			$netobjdata['invprojectid'] = intval($ipi);
		} else {
			$netobjdata['invprojectid'] = NULL;
		}
		if ($netobjdata['netnodeid']=="-1") {
			$netobjdata['netnodeid']=NULL;
			$netnodeid = $DB->GetOne("SELECT netnodeid FROM netobjices WHERE id = ?", array($netobjdata['id']));
			if ($netnodeid) {
				/* Był jakiś węzeł i został usunięty */
				$netobjdata['location'] = '';
				$netobjdata['location_city'] = null;
				$netobjdata['location_street'] = null;
				$netobjdata['location_house'] = null;
				$netobjdata['location_flat'] = null;
				$netobjdata['longitude'] = null;
            			$netobjdata['latitude'] = null;
			}
		} else {
			/* dziedziczenie lokalizacji */
			$dev = $DB->GetRow("SELECT * FROM netnodes n WHERE id = ?", array($netobjdata['netnodeid']));
			if ($dev) {
				if (!strlen($netobjdata['location'])) {
					$netobjdata['location'] = $dev['location'];
					$netobjdata['location_city'] = $dev['location_city'];
					$netobjdata['location_street'] = $dev['location_street'];
					$netobjdata['location_house'] = $dev['location_house'];
					$netobjdata['location_flat'] = $dev['location_flat'];
				}
				if (!strlen($netobjdata['longitude']) || !strlen($netobjdata['latitude'])) {
					$netobjdata['longitude'] = $dev['longitude'];
					$netobjdata['latitude'] = $dev['latitude'];
				}
			}
		}

		$LMS->NetObjUpdate($netobjdata);
		$LMS->CleanupInvprojects();
		$hook_data = $LMS->executeHook('netobjedit_after_update',
			array(
				'smarty' => $SMARTY,
			));
		$SESSION->redirect('?m=netobjinfo&id=' . $_GET['id']);
	}
} else {
	$netobjdata = $LMS->GetNetObj($_GET['id']);

	if ($netobjdata['purchasetime'])
		$netobjdata['purchasedate'] = date('Y/m/d', $netobjdata['purchasetime']);

	if ($netobjdata['city_name'] || $netobjdata['street_name']) {
		$netobjdata['teryt'] = true;
		$netobjdata['location'] = location_str($netobjdata);
	}
        switch ($netobjdata['type']) {
                case 0:
			$netobjdata['reserve_quantity']=$netobjdata['parameter'];
                        break;
                case 1:
			$netobjdata['closure_capacity']=$netobjdata['parameter'];
                        break;
                case 2:
			$netobjdata['box_adaptors']=$netobjdata['parameter'];
                        break;
                case 3:
			$data=preg_split('/:/',$netobjdata['parameter']);
			$netobjdata['splitter_in']=$data[0];
			$netobjdata['splitter_out']=$data[1];
                        break;
                default:
        }

}

$netobjdata['id'] = $_GET['id'];

unset($netobjlist['total']);
unset($netobjlist['order']);
unset($netobjlist['direction']);


$layout['pagetitle'] = trans('Object Edit: $a ($b)', $netobjdata['name'], $netobjdata['producer']);

if ($subtitle)
	$layout['pagetitle'] .= ' - ' . $subtitle;

$nprojects = $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name",
	array(INV_PROJECT_SYSTEM));
$SMARTY->assign('NNprojects',$nprojects);
$netnodes = $DB->GetAll("SELECT * FROM netnodes ORDER BY name");
$SMARTY->assign('NNnodes',$netnodes);


$SMARTY->assign('error', $error);
$SMARTY->assign('netobjinfo', $netobjdata);
$SMARTY->assign('objectid', $netobjdata['id']);

switch ($edit) {
	case 'data':
		$SMARTY->display('netobj/netobjedit.html');
		break;
	default:
		$SMARTY->display('netobj/netobjinfo.html');
		break;
}
?>
