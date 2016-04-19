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

$layout['pagetitle'] = trans('Network Elements');

if(!isset($_GET['o']))
	$SESSION->restore('nelo', $o);
else
	$o = $_GET['o'];
$SESSION->save('nelo', $o);

if(!isset($_GET['t']))
        $SESSION->restore('neft', $t);
else
        $t = $_GET['t'];
$SESSION->save('neft', $t);

if(!isset($_GET['s']))
	$SESSION->restore('nefs', $s);
else
	$s = $_GET['s'];
$SESSION->save('nefs', $s);

if(!isset($_GET['p']))
	$SESSION->restore('nefp', $p);
else
	$p = $_GET['p'];
$SESSION->save('nefp', $p);

if(!isset($_GET['n']))
	$SESSION->restore('nefn', $n);
else
	$n = $_GET['n'];
$SESSION->save('nefn', $n);

if(!isset($_GET['producer']))
	$SESSION->restore('nefproducer', $producer);
else
	$producer = $_GET['producer'];
$SESSION->save('nefproducer', $producer);

if(!isset($_GET['model']))
	$SESSION->restore('nefmodel', $model);
else
	$model = $_GET['model'];
$SESSION->save('nefmodel', $model);

if (empty($model))
	$model = -1;
if (empty($producer))
	$producer = -1;

$producers = $DB->GetCol("SELECT DISTINCT UPPER(TRIM(producer)) AS producer FROM netelements WHERE producer <> '' ORDER BY producer");
$models = $DB->GetCol("SELECT DISTINCT UPPER(TRIM(model)) AS model FROM netelements WHERE model <> ''"
	. ($producer != '-1' ? " AND UPPER(TRIM(producer)) = " . $DB->Escape($producer == '-2' ? '' : $producer) : '') . " ORDER BY model");
if (!preg_match('/^-[0-9]+$/', $model) && !in_array($model, $models)) {
	$SESSION->save('nefmodel', '-1');
	$SESSION->redirect('?' . preg_replace('/&model=[^&]+/', '', $_SERVER['QUERY_STRING']));
}
if (!preg_match('/^-[0-9]+$/', $producer) && !in_array($producer, $producers)) {
	$SESSION->save('nefproducer', '-1');
	$SESSION->redirect('?' . preg_replace('/&producer=[^&]+/', '', $_SERVER['QUERY_STRING']));
}

$search = array(
	'type' => $t,
	'status' => $s,
	'project' => $p,
	'netnode' => $n,
	'producer' => $producer,
	'model' => $model,
);
$netelemlist = $LMS->GetNetElemList($o, $search);
$listdata['total'] = $netelemlist['total'];
$listdata['order'] = $netelemlist['order'];
$listdata['direction'] = $netelemlist['direction'];
$listdata['type'] = $t;
$listdata['status'] = $s;
$listdata['invprojectid'] = $p;
$listdata['netnode'] = $n;
$listdata['producer'] = $producer;
$listdata['model'] = $model;
unset($netelemlist['total']);
unset($netelemlist['order']);
unset($netelemlist['direction']);

if(!isset($_GET['page']))
        $SESSION->restore('nelp', $_GET['page']);
	
$page = (! $_GET['page'] ? 1 : $_GET['page']);
$pagelimit = ConfigHelper::getConfig('phpui.nodelist_pagelimit', $listdata['total']);
$start = ($page - 1) * $pagelimit;

$SESSION->save('nelp', $page);

$SESSION->save('backto', $_SERVER['QUERY_STRING']);

$SMARTY->assign('page',$page);
$SMARTY->assign('pagelimit',$pagelimit);
$SMARTY->assign('start',$start);
$SMARTY->assign('netelemlist',$netelemlist);
$SMARTY->assign('listdata',$listdata);
$SMARTY->assign('netnodes', $DB->GetAll("SELECT id, name FROM netnodes ORDER BY name"));
$SMARTY->assign('NNprojects', $DB->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name",
	array(INV_PROJECT_SYSTEM)));
$SMARTY->assign('producers', $producers);
$SMARTY->assign('models', $models);
$SMARTY->display('netelements/netelemlist.html');

?>
