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

$layout['pagetitle'] = trans('Network Cables');

if(!isset($_GET['o']))
	$SESSION->restore('ndlo', $o);
else
	$o = $_GET['o'];
$SESSION->save('ndlo', $o);

if(!isset($_GET['s']))
	$SESSION->restore('ndfs', $s);
else
	$s = $_GET['s'];
$SESSION->save('ndfs', $s);

if(!isset($_GET['p']))
	$SESSION->restore('ndfp', $p);
else
	$p = $_GET['p'];
$SESSION->save('ndfp', $p);

if(!isset($_GET['n']))
	$SESSION->restore('ndfn', $n);
else
	$n = $_GET['n'];
$SESSION->save('ndfn', $n);

if(!isset($_GET['producer']))
	$SESSION->restore('ndfproducer', $producer);
else
	$producer = $_GET['producer'];
$SESSION->save('ndfproducer', $producer);

if(!isset($_GET['model']))
	$SESSION->restore('ndfmodel', $model);
else
	$model = $_GET['model'];
$SESSION->save('ndfmodel', $model);

if (empty($model))
	$model = -1;
if (empty($producer))
	$producer = -1;

$producers = $DB->GetCol("SELECT DISTINCT UPPER(TRIM(producer)) AS producer FROM netcables WHERE producer <> '' ORDER BY producer");
$models = $DB->GetCol("SELECT DISTINCT UPPER(TRIM(model)) AS model FROM netcables WHERE model <> ''"
	. ($producer != '-1' ? " AND UPPER(TRIM(producer)) = " . $DB->Escape($producer == '-2' ? '' : $producer) : '') . " ORDER BY model");
if (!preg_match('/^-[0-9]+$/', $model) && !in_array($model, $models)) {
	$SESSION->save('ndfmodel', '-1');
	$SESSION->redirect('?' . preg_replace('/&model=[^&]+/', '', $_SERVER['QUERY_STRING']));
}
if (!preg_match('/^-[0-9]+$/', $producer) && !in_array($producer, $producers)) {
	$SESSION->save('ndfproducer', '-1');
	$SESSION->redirect('?' . preg_replace('/&producer=[^&]+/', '', $_SERVER['QUERY_STRING']));
}

$search = array(
	'status' => $s,
	'project' => $p,
	'producer' => $producer,
	'model' => $model,
);
$netcablist = $LMS->GetNetCabList($o, $search);
$listdata['total'] = $netcablist['total'];
$listdata['order'] = $netcablist['order'];
$listdata['direction'] = $netcablist['direction'];
$listdata['status'] = $s;
$listdata['invprojectid'] = $p;
$listdata['producer'] = $producer;
$listdata['model'] = $model;
unset($netcablist['total']);
unset($netcablist['order']);
unset($netcablist['direction']);

if(!isset($_GET['page']))
        $SESSION->restore('ndlp', $_GET['page']);
	
$page = (! $_GET['page'] ? 1 : $_GET['page']);
$pagelimit = ConfigHelper::getConfig('phpui.nodelist_pagelimit', $listdata['total']);
$start = ($page - 1) * $pagelimit;

$SESSION->save('ndlp', $page);

$SESSION->save('backto', $_SERVER['QUERY_STRING']);

$SMARTY->assign('page',$page);
$SMARTY->assign('pagelimit',$pagelimit);
$SMARTY->assign('start',$start);
$SMARTY->assign('netcablist',$netcablist);
$SMARTY->assign('listdata',$listdata);
$SMARTY->assign('NNprojects', $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name",
	array(INV_PROJECT_SYSTEM)));
$SMARTY->assign('producers', $producers);
$SMARTY->assign('models', $models);
$SMARTY->display('netcab/netcablist.html');

?>
