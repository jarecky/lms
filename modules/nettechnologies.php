<?php

$action = !empty($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	case 'edit':
		$layout['pagetitle'] = trans('Edit network technology');
		$template='nettechnologyedit.html';
		

		break;
	case 'clone':
		$layout['pagetitle'] = trans('Clone network technology');
		$template='nettechnologyedit.html';


	break;
	case 'add':
		$layout['pagetitle'] = trans('Add network technology');
		$template='nettechnologyedit.html';

	break;
	default: 
		$layout['pagetitle'] = trans('List of network technologies');
		$template='nettechnologies.html';

		if(!isset($_GET['o']))
			$SESSION->restore('ntlo', $o);
		else
			$o = $_GET['o'];
		$SESSION->save('ntlo', $o);


		if(!isset($_GET['wiretype']))
			$SESSION->restore('ntfwiretype', $wiretype);
		else
			$wiretype = $_GET['wiretype'];
		$SESSION->save('ntfwiretype', $wiretype);

		$qw='';
		if ($wiretype>=0) {
			$qw="WHERE wiretype=".$wiretype;
		}
		$qo='';
		if (isset($o)) {
			list($order, $direction) = preg_split('/,/',$o);
			$qo="ORDER BY n.$order $direction";
		}

		$nettechnologies=$DB->GetAll("SELECT 
			n.*,
			COUNT(p.id) AS ports
			FROM nettechnologies n
			LEFT JOIN netports p ON n.id=p.technology
			$qw GROUP BY n.id $qo");

		$listdata['total'] = sizeof($nettechnologies);
		$listdata['order'] = (isset($order) ? $order : '');
		$listdata['direction'] = (isset($direction) ? $direction : 'asc');
		$listdata['wiretype'] = $wiretype;

		if(!isset($_GET['page']))
			$SESSION->restore('ntlp', $_GET['page']);

		$page = (! $_GET['page'] ? 1 : $_GET['page']);
		$pagelimit = ConfigHelper::getConfig('phpui.nodelist_pagelimit', $listdata['total']);
		$start = ($page - 1) * $pagelimit;

		$SESSION->save('ntlp', $page);

		$SMARTY->assign('page',$page);
		$SMARTY->assign('pagelimit',$pagelimit);
		$SMARTY->assign('start',$start);
		$SMARTY->assign("listdata", $listdata);
		$SMARTY->assign("nettechnologies", $nettechnologies);
		break;
}


$SMARTY->display("netelements/".$template);

?>
