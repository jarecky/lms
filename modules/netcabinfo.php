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

if (!$LMS->NetCabExists($_GET['id'])) {
	$SESSION->redirect('?m=netcablist');
}

$netcabinfo = $LMS->GetNetCab($_GET['id']);

if ($netcabinfo['purchasetime'])
	$netcabinfo['purchasedate'] = date('Y/m/d', $netcabinfo['purchasetime']);

$layout['pagetitle'] = trans('Cable Info: $a ($b)', $netcabinfo['name'], $netcabinfo['producer']);

$netcabinfo['projectname'] = trans('none');
if ($netcabinfo['invprojectid']) {
        $prj = $DB->GetRow("SELECT * FROM invprojects WHERE id = ?", array($netcabinfo['invprojectid']));
        if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                        /* inherited */
                        if ($netnode) {
                                $prj = $DB->GetRow("SELECT * FROM invprojects WHERE id=?",
                                        array($netnode['invprojectid']));
                                if ($prj)
                                        $netcabinfo['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                        }
                } else
                        $netcabinfo['projectname'] = $prj['name'];
        }
}

$src=$LMS->GetNetObj($netcabinfo['src']);
$SMARTY->assign('src',$src);
$dst=$LMS->GetNetObj($netcabinfo['dst']);
$SMARTY->assign('dst',$dst);
$SMARTY->assign('netcabinfo', $netcabinfo);
$SMARTY->assign('cabid', $netcabinfo['id']);

$SMARTY->display('netcab/netcabinfo.html');


?>
