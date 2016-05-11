<?php

/*
 *  LMS version 1.11-git
 *
 *  Copyright (C) 2001-2013 LMS Developers
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

/**
 * LMSNetElemAction
 *
 * @author Jaroslaw Dziubek <jaroslaw.dziubek@perfect.net.pl> 
 */
class LMSNetElemAction extends LMSModuleAction 
{
	public function Route() {
		if(!isset($_GET['action'])) $_GET['action']='';
		switch ($_GET['action']) {
			case 'list':
				$this->_list();
				break;
			case 'add':
				$this->_add();
				break;
			#case 'edit':
			#	$this->_edit();
			#	break;
			case 'info':
				$this->_info();
				break;
			case 'models':
				$this->_models();
				break;
			case 'connect':
				$this->_connect();
				break;
			default:
				$this->_error();
				break;
		}
	}

	function _list() {
		$layout['pagetitle'] = trans('Network Elements');

		if(!isset($_GET['o']))
			$this->session->restore('nelo', $o);
		else
			$o = $_GET['o'];
		$this->session->save('nelo', $o);

		if(!isset($_GET['t']))
			$this->session->restore('neft', $t);
		else
			$t = $_GET['t'];
		$this->session->save('neft', $t);

		if(!isset($_GET['s']))
			$this->session->restore('nefs', $s);
		else
			$s = $_GET['s'];
		$this->session->save('nefs', $s);

		if(!isset($_GET['p']))
			$this->session->restore('nefp', $p);
		else
			$p = $_GET['p'];
		$this->session->save('nefp', $p);

		if(!isset($_GET['n']))
			$this->session->restore('nefn', $n);
		else
			$n = $_GET['n'];
		$this->session->save('nefn', $n);

		if(!isset($_GET['producer']))
			$this->session->restore('nefproducer', $producer);
		else
			$producer = $_GET['producer'];
		$this->session->save('nefproducer', $producer);

		if(!isset($_GET['model']))
			$this->session->restore('nefmodel', $model);
		else
			$model = $_GET['model'];
		$this->session->save('nefmodel', $model);

		if (empty($model))
			$model = -1;
		if (empty($producer))
			$producer = -1;

		$producers = $this->db->GetCol("SELECT DISTINCT UPPER(TRIM(producer)) AS producer FROM netelements WHERE producer <> '' ORDER BY producer");
		$models = $this->db->GetCol("SELECT DISTINCT UPPER(TRIM(model)) AS model FROM netelements WHERE model <> ''"
			. ($producer != '-1' ? " AND UPPER(TRIM(producer)) = " . $this->db->Escape($producer == '-2' ? '' : $producer) : '') . " ORDER BY model");
		if (!preg_match('/^-[0-9]+$/', $model) && !in_array($model, $models)) {
			$this->session->save('nefmodel', '-1');
			$this->session->redirect('?' . preg_replace('/&model=[^&]+/', '', $_SERVER['QUERY_STRING']));
		}
		if (!preg_match('/^-[0-9]+$/', $producer) && !in_array($producer, $producers)) {
			$this->session->save('nefproducer', '-1');
			$this->session->redirect('?' . preg_replace('/&producer=[^&]+/', '', $_SERVER['QUERY_STRING']));
		}

		$search = array(
			'type' => $t,
			'status' => $s,
			'project' => $p,
			'netnode' => $n,
			'producer' => $producer,
			'model' => $model,
		);
		$netelemlist = $this->lms->GetNetElemList($o, $search);
		foreach ($netelemlist AS $id => $netelem) {
			if (isset($netelem['type']))
			switch ($netelem['type']) {
			case '0':
				$netelemlist[$id]['ports']=$this->lms->GetNetElemPorts($netelem['id']);
				$netelemlist[$id]['copper']=0;
				$netelemlist[$id]['copper_taken']=0;
				$netelemlist[$id]['wireless']=0;
				$netelemlist[$id]['wireless_taken']=0;
				$netelemlist[$id]['fiber']=0;
				$netelemlist[$id]['fiber_taken']=0;
				if (is_array($netelemlist[$id]['ports'])) 
				foreach ($netelemlist[$id]['ports'] as $port) {
					if ($port['technology']<100) {
						$netelemlist[$id]['copper']++;
						if ($port['taken']==$port['capacity']) 
							$netelemlist[$id]['copper_taken']++;
					} elseif ($port['technology']<200) {
						if (isset($port['radiosector'])) {
							$port['radiosector']['taken']=$port['taken'];
							$port['radiosector']['capacity']=$port['capacity'];
							$netelemlist[$id]['radiosectors'][]=$port['radiosector'];
						} else {
							$netelemlist[$id]['wireless']++;
							if ($port['taken']==$port['capacity'])
								$netelemlist[$id]['wireless_taken']++;
						}
					} else {
						$netelemlist[$id]['fiber']++;
						if ($port['taken']==$port['capacity'])
							$netelemlist[$id]['fiber_taken']++;
					}
				}
				break;
			case '1':
				$netelemlist[$id]['ports']=$this->lms->GetNetElemPorts($netelem['id']);
				if (is_array($netelemlist[$id]['ports']))
				foreach ($netelemlist[$id]['ports'] as $port) {
					if ($port['connectortype']==999)
						$netelemlist[$id]['conn'][$port['connectortype']]['total']+=$port['capacity'];
					else
						$netelemlist[$id]['conn'][$port['connectortype']]['total']++;
					if ($port['taken']==$port['capacity'])
						$netelemlist[$id]['conn'][$port['connectortype']]['taken']++;
				}
				#echo '<PRE>';print_r($netelemlist[$id]);echo '</PRE>';
				break;
			case '2':
				$netelemlist[$id]['netcable']=$this->lms->GetNetElemCable($netelem['id']);
				break;
			case '3':
				$netelemlist[$id]['netsplitter']=$this->lms->GetNetElemSplitter($netelem['id']);
				break;
			case '4':
				$netelemlist[$id]['netmultiplexer']=$this->lms->GetNetElemMultiplexer($netelem['id']);
				break;
			case '99':
				$netelemlist[$id]['ports']=$this->lms->GetNetElemPorts($netelem['id']);
				break;
			}
		}
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
			$this->session->restore('nelp', $_GET['page']);

		$page = (! $_GET['page'] ? 1 : $_GET['page']);
		$pagelimit = ConfigHelper::getConfig('phpui.nodelist_pagelimit', $listdata['total']);
		$start = ($page - 1) * $pagelimit;

		$this->session->save('nelp', $page);

		$this->session->save('backto', $_SERVER['QUERY_STRING']);

		$this->smarty->assign('page',$page);
		$this->smarty->assign('pagelimit',$pagelimit);
		$this->smarty->assign('start',$start);
		$this->smarty->assign('netelemlist',$netelemlist);
		$this->smarty->assign('listdata',$listdata);
		$this->smarty->assign('netnodes', $this->db->GetAll("SELECT id, name FROM netnodes ORDER BY name"));
		$this->smarty->assign('NNprojects', $this->db->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name",
			array(INV_PROJECT_SYSTEM)));
		$this->smarty->assign('producers', $producers);
		$this->smarty->assign('models', $models);
		$this->smarty->display('netelements/list.html');
	}

	function _add() {
		include(MODULES_DIR . '/netelemxajax.inc.php');
		$netelemdata['type']=-1;
		global $NETPORTTYPES, $NETCONNECTORS, $NETTECHNOLOGIES;

		if(isset($_POST['netelem'])) {
		print_r($_POST);
			$netelemdata = $_POST['netelem'];
			if($netelemdata['name'] == '')
				$error['name'] = trans('Element name is required!');
			elseif (strlen($netelemdata['name']) > 60)
				$error['name'] = trans('Specified name is too long (max. $a characters)!', '60');

			$netelemdata['purchasetime'] = 0;
			if($netelemdata['purchasedate'] != '') {
				// date format 'yyyy/mm/dd'
				if(!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}$/', $netelemdata['purchasedate'])) {
					$error['purchasedate'] = trans('Invalid date format!');
				} else {
					$date = explode('/', $netelemdata['purchasedate']);
					if(checkdate($date[1], $date[2], (int)$date[0])) {
						$tmpdate = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
						if(mktime(0,0,0) < $tmpdate)
							$error['purchasedate'] = trans('Date from the future not allowed!');
						else
							$netelemdata['purchasetime'] = $tmpdate;
					} else
						$error['purchasedate'] = trans('Invalid date format!');
				}
			}

			if($netelemdata['guaranteeperiod'] != 0 && $netelemdata['purchasetime'] == NULL) {
				$error['purchasedate'] = trans('Purchase date cannot be empty when guarantee period is set!');
			}


			if ($netelemdata['invprojectid'] == '-1') { // nowy projekt
				if (!strlen(trim($netelemdata['projectname']))) {
				 $error['projectname'] = trans('Project name is required');
				}
				$l = $this->db->GetOne("SELECT * FROM invprojects WHERE name=? AND type<>?",
					array($netelemdata['projectname'], INV_PROJECT_SYSTEM));
				if (sizeof($l)>0) {
					$error['projectname'] = trans('Project with that name already exists');
				}
			}

			if ($netelemdata['type']==0) {
			// AKTYWNE
				$netactivedata=$_POST['netactive'];
				$netportdata=$_POST['netports'];
				$netelemdata['netnodeid']=$netactivedata['netnodeid'];
				$netactivedata['ports']=$netportdata;
				if(empty($netactivedata['clients']))
					$netactivedata['clients'] = 0;
				else
					$netactivedata['clients'] = intval($netactivedata['clients']);
                                if(!isset($netactivedata['shortname'])) $netactivedata['shortname'] = '';
                                if(!isset($netactivedata['secret'])) $netactivedata['secret'] = '';
                                if(!isset($netactivedata['community'])) $netactivedata['community'] = '';
                                if(!isset($netactivedata['nastype'])) $netactivedata['nastype'] = 0;
				$this->smarty->assign('netports', $netportdata);
				$this->smarty->assign('netactive', $netactivedata);
			} elseif ($netelemdata['type']==1) {
			// PASYWNE
				$netpassivedata=$_POST['netpassive'];
				$netportdata=$_POST['netports'];
				$netelemdata['netnodeid']=$netpassivedata['netnodeid'];
				$netpassivedata['ports']=$netportdata;
				$this->smarty->assign('netports', $netportdata);
				$this->smarty->assign('netpassive', $netpassivedata);
			} elseif ($netelemdata['type']==2) {
			// KABEL
				$netcabledata=$_POST['netcable'];
				$netelemdata['netnodeid']=$netcabledata['srcnodeid'];
				if (!is_numeric($netcabledata['distance']))
					$error['distance']=trans('Distance must be integer number!');
				if (!is_numeric($netcabledata['capacity']))
					$error['capacity']=trans('Number of wires must be integer number!');
				#elseif ($netcabledata['capacity']%12!=0 AND $netcabledata['type']==201) 
				#	$error['capacity']=trans('Wrong number of wires');
				if ($netcabledata['srcnodeid']==$netcabledata['dstnodeid'])
					$error['dstnodeid']=trans('Begin and end node must be different!');
				$this->smarty->assign('netcable', $netcabledata);
			} elseif ($netelemdata['type']==3) {
			// SPLITTER
				$netsplitterdata=$_POST['netsplitter'];
				$netelemdata['netnodeid']=$netsplitterdata['netnodeid'];
				if (!is_numeric($netsplitterdata['in']))
					$error['in']=trans("Number of 'in' ports must be integer number!");
				if (!is_numeric($netsplitterdata['in']))
					$error['in']=trans("Number of 'in' ports must be integer number!");
				$this->smarty->assign('netsplitter', $netsplitterdata);
			} elseif ($netelemdata['type']==4) {
			// MULTIPLEXER
                                $netmultiplexerdata=$_POST['netmultiplexer'];
				$netelemdata['netnodeid']=$netmultiplexerdata['netnodeid'];
                                if (!is_numeric($netmultiplexerdata['in']))
                                        $error['in']=trans("Number of 'in' ports must be integer number!");
                                if (!is_numeric($netmultiplexerdata['in']))
                                        $error['in']=trans("Number of 'in' ports must be integer number!");
                                $this->smarty->assign('netmultiplexer', $netmultiplexerdata);
			} elseif ($netelemdata['type']==99) {
			// COMPUTER
				$netcomputerdata=$_POST['netcomputer'];
				$netelemdata['netnodeid']=$netcomputerdata['netnodeid'];
				$this->smarty->assign('netcomputer',$netcomputerdata);
			} else {
				$error['type']=trans('Error');
			}
			
			if (!$error) {
				if($netelemdata['guaranteeperiod'] == -1)
					$netelemdata['guaranteeperiod'] = NULL;

				$ipi = $netelemdata['invprojectid'];
				if ($ipi == '-1') {
					$this->db->BeginTrans();
					$this->db->Execute("INSERT INTO invprojects (name, type) VALUES (?, ?)",
						array($netelemdata['projectname'], INV_PROJECT_REGULAR));
					$ipi = $this->db->GetLastInsertID('invprojects');
					$this->db->CommitTrans();
				} 
				if ($netelemdata['invprojectid'] == '-1' || intval($ipi)>0)
					$netelemdata['invprojectid'] = intval($ipi);
				else
					$netelemdata['invprojectid'] = NULL;

				switch ($netelemdata['type']) {
				case '0':
					$netelemid = $this->lms->NetElemAddActive($netelemdata,$netactivedata);
					break;
				case '1':
					$netelemid = $this->lms->NetElemAddPassive($netelemdata,$netpassivedata);
					break;
				case '2':
					$netelemid = $this->lms->NetElemAddCable($netelemdata,$netcabledata);
					break;
				case '3':
					$netelemid = $this->lms->NetElemAddSplitter($netelemdata,$netsplitterdata);
					break;
				case '4':
					$netelemid = $this->lms->NetElemAddMultiplexer($netelemdata,$netmultiplexerdata);
					break;
				case '99':
					$netelemid = $this->lms->NetElemAddComputer($netelemdata,$netcomputerdata);
					break;
				}
				if ($netelemid)
					$this->session->redirect('?m=netelement&action=info&id='.$netelemid);
			}
		
			$this->smarty->assign('error', $error);
			$this->smarty->assign('netelem', $netelemdata);
		} 
		elseif (isset($_GET['id'])) {
			#$netelemdata = $this->lms->GetNetElem($_GET['id']);
			#$netelemdata['name'] = trans('$a (clone)', $netelemdata['name']);
			#$netelemdata['teryt'] = !empty($netelemdata['location_city']) && !empty($netelemdata['location_street']);
			
		}
		$this->smarty->assign('netelem', $netelemdata);
		$this->smarty->assign('NETPORTTYPES',$NETPORTTYPES);
		$this->smarty->assign('NETCONNECTORS',$NETCONNECTORS);
		$this->smarty->assign('NETTECHNOLOGIES',$NETTECHNOLOGIES);
		$layout['pagetitle'] = trans('New Element');

		$this->smarty->assign('nastype', $this->lms->GetNAStypes());

		$nprojects = $this->db->GetAll("SELECT * FROM invprojects WHERE type<>? ORDER BY name", array(INV_PROJECT_SYSTEM));
		$this->smarty->assign('NNprojects',$nprojects);
		$netnodes = $this->db->GetAll("SELECT * FROM netnodes ORDER BY name");
		$this->smarty->assign('NNnodes',$netnodes);

		if (ConfigHelper::checkConfig('phpui.ewx_support'))
			$this->smarty->assign('channels', $this->db->GetAll('SELECT id, name FROM ewx_channels ORDER BY name'));

		$this->smarty->display("netelements/add.html");
	}	

	function _edit() {

	} 

	function _info() {
		if (!$this->lms->NetElemExists($_GET['id'])) {
			$this->session->redirect('?m=netelement&action=list');
		}

		include(MODULES_DIR . '/netelemxajax.inc.php');

		if (!array_key_exists('xjxfun', $_POST)) {                  // xajax was called and handled by netelemxajax.inc.php
			$layout['pagetitle'] = trans('Element Info: $a $b $c', $neteleminfo['name'], $neteleminfo['producer'], $neteleminfo['model']);
			$this->session->save('backto', $_SERVER['QUERY_STRING']);
			switch ($this->lms->GetNetElemType($_GET['id'])) { 
			case '0': 
				$neteleminfo = $this->lms->GetNetElemActive($_GET['id']);
				$neteleminfo['id'] = $_GET['id'];
				$neteleminfo['projectname'] = trans('none');
				if ($neteleminfo['invprojectid']) {
					$prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($neteleminfo['invprojectid']));
					if ($prj) {
						if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
							/* inherited */
							if ($netnode) {
								$prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",
									array($netnode['invprojectid']));
								if ($prj)
									$neteleminfo['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
							}
						} else
							$neteleminfo['projectname'] = $prj['name'];
					}
				}

				$netelemconnected = $this->lms->GetNetElemConnectedNames($_GET['id']);
				$netcomplist = $this->lms->GetNetElemLinkedNodes($_GET['id']);
				$netelemlist = $this->lms->GetNotConnectedElements($_GET['id']);
				$netports = $this->lms->GetNetElemPorts($_GET['id']);

				$nodelist = $this->lms->GetUnlinkedNodes();
				$netelemips = $this->lms->GetNetElemIPs($_GET['id']);

				$this->smarty->assign('neteleminfo', $neteleminfo);
				$this->smarty->assign('objectid', $neteleminfo['id']);
				$this->smarty->assign('restnetelemlist', $netelemlist);
				$this->smarty->assign('netports', $netports);
				$this->smarty->assign('netelemips', $netelemips);
				$this->smarty->assign('nodelist', $nodelist);
				$this->smarty->assign('elemlinktype', $this->session->get('elemlinktype'));
				$this->smarty->assign('elemlinktechnology', $this->session->get('elemlinktechnology'));
				$this->smarty->assign('elemlinkspeed', $this->session->get('elemlinkspeed'));
				$this->smarty->assign('nodelinktype', $this->session->get('nodelinktype'));
				$this->smarty->assign('nodelinktechnology', $this->session->get('nodelinktechnology'));
				$this->smarty->assign('nodelinkspeed', $this->session->get('nodelinkspeed'));

				$hook_data = $this->lms->executeHook('neteleminfo_before_display',
					array(
						'netelemconnected' => $netelemconnected,
						'netcomplist' => $netcomplist,
						'smarty' => $this->smarty,
					)
				);
				$netelemconnected = $hook_data['netelemconnected'];
				$netcomplist = $hook_data['netcomplist'];
				$this->smarty->assign('netelemlist', $netelemconnected);
				$this->smarty->assign('netcomplist', $netcomplist);

				if (isset($_GET['ip'])) {
					$nodeipdata = $this->lms->GetNodeConnType($_GET['ip']);
					$netelemauthtype = array();
					$authtype = $nodeipdata;
					if ($authtype != 0) {
						$netelemauthtype['dhcp'] = ($authtype & 2);
						$netelemauthtype['eap'] = ($authtype & 4);
					}
					$this->smarty->assign('nodeipdata', $this->lms->GetNode($_GET['ip']));
					$this->smarty->assign('netelemauthtype', $netelemauthtype);
					$this->smarty->display('netelements/ipinfo.html');
				} else {
					$this->smarty->display('netelements/activeinfo.html');
				}
				break;
			case '1':
				$neteleminfo=$this->lms->GetNetElemPassive($_GET['id']);
				$netports=$this->lms->GetNetElemPorts($_GET['id']);

				$this->smarty->assign('netports',$netports);
				$this->smarty->assign('neteleminfo',$neteleminfo);
				$this->smarty->display('netelements/passiveinfo.html');
				break;
			case '2':
				$neteleminfo=$this->lms->GetNetElemCable($_GET['id']);
				$netwires=$this->db->GetAll("SELECT * FROM netwires WHERE netcableid=?",array($_GET['id']));
				if (count($netwires)) 
				foreach ($netwires AS $id => $netwire) {
					$netwires[$id]['conn']=$this->lms->GetConnectionForWire($netwire['id'],$neteleminfo['srcnodeid'],$neteleminfo['dstnodeid']);
				}
				$srcnode=$this->db->GetRow("SELECT * FROM netnodes WHERE id=".$neteleminfo['srcnodeid']);
				$dstnode=$this->db->GetRow("SELECT * FROM netnodes WHERE id=".$neteleminfo['dstnodeid']);
				#$srcnodeelemlist=$this->lms->GetNetElemUnconnectedList($neteleminfo['srcnodeid'],$_GET['id']);
				#$dstnodeelemlist=$this->lms->GetNetElemUnconnectedList($neteleminfo['dstnodeid'],$_GET['id']);
				$this->smarty->assign('neteleminfo',$neteleminfo);
				$this->smarty->assign('netwires',$netwires);
				$this->smarty->assign('srcnode',$srcnode);
				$this->smarty->assign('dstnode',$dstnode);
				#$this->smarty->assign('srcnodeelemlist',$srcnodeelemlist);
				#$this->smarty->assign('dstnodeelemlist',$dstnodeelemlist);
				$this->smarty->assign('colorschema',$colorschema);
                                $hook_data = $this->lms->executeHook('neteleminfo_before_display',
                                        array(
                                                'smarty' => $this->smarty,
                                        )
                                );

				$this->smarty->display('netelements/cableinfo.html');
				break;
			case '3':
				$neteleminfo=$this->lms->GetNetElemSplitter($_GET['id']);
				$netport=$this->lms->GetNetElemPorts($_GET['id']);
				foreach ($netport AS $port) {
					if ($port['type']==201) {
						$netports['in'][]=$port;
					} elseif ($port['type']==202) {
						$netports['out'][]=$port;
					}
				}
				$this->smarty->assign('neteleminfo',$neteleminfo);
				$this->smarty->assign('netports',$netports);
				$this->smarty->display('netelements/splitterinfo.html');
				break;
			case '4':
                                $neteleminfo=$this->lms->GetNetElemMultiplexer($_GET['id']);
                                $netport=$this->lms->GetNetElemPorts($_GET['id']);
                                foreach ($netport AS $port) {
                                        if ($port['type']==201) {
                                                $netports['in'][]=$port;
                                        } elseif ($port['type']==202) {
                                                $netports['out'][]=$port;
                                        }
                                }

                                $this->smarty->assign('neteleminfo',$neteleminfo);
                                $this->smarty->assign('netports',$netports);
                                $this->smarty->display('netelements/multiplexerinfo.html');				
				break;
			case '99':
				break;
			default:
				#$this->session->redirect('?m=netelement&action=list');	
			}
		}
	}

	function _models() {

		include(MODULES_DIR . '/modelxajax.inc.php');
		global $NETPORTTYPES, $NETCONNECTORS, $NETELEMENTTYPES;
		$layout['pagetitle'] = trans("Network device producers and models");
		$listdata = $modellist = array();
		$producerlist = $this->db->GetAll('SELECT id, name FROM netdeviceproducers ORDER BY name ASC');


		if (!isset($_GET['p_id'])) 
			$this->session->restore('ndpid', $pid);
		else
			$pid = intval($_GET['p_id']);
		$this->session->save('ndpid', $pid);

		if (!isset($_GET['page']))
			$this->session->restore('ndlpage', $_GET['page']);

		if ($pid)
			$producerinfo = $this->db->GetRow('SELECT p.id , p.alternative_name FROM netdeviceproducers p WHERE p.id = ?', array($pid));
		else
			$producerinfo = array();

		$listdata['pid'] = $pid; // producer id

		$this->smarty->assign('NETPORTTYPES',$NETPORTTYPES);
		$this->smarty->assign('NETELEMENTTYPES',$NETELEMENTTYPES);
		$this->smarty->assign('NETCONNECTORS',$NETCONNECTORS);

                $modellist = $this->lms->GetModelList($pid);

                $listdata['total'] = sizeof($modellist);

                $page = (!$_GET['page'] ? 1 : $_GET['page']);
                $pagelimit = ConfigHelper::getConfig('phpui.netdevmodel_pagelimit', $listdata['total']);
                $start = ($page - 1) * $pagelimit;

                $this->session->save('ndlpage',$page);

                $this->smarty->assign('xajax', $this->lms->RunXajax());
                $this->smarty->assign('listdata',$listdata);
                $this->smarty->assign('producerlist',$producerlist);
                $this->smarty->assign('modellist',$modellist);
                $this->smarty->assign('producerinfo',$producerinfo);
                $this->smarty->assign('pagelimit',$pagelimit);
                $this->smarty->assign('page',$page);
                $this->smarty->assign('start',$start);
                $this->smarty->display('netelements/models.html');
	}		

	function _connect() {
		global $NETTECHNOLOGIES;
		include(MODULES_DIR . '/netelemconnect.inc.php');
		if (isset($_GET['wireid'])) {
			$connlist=$this->lms->GetConnPossForWire($_GET['netnodeid'],$_GET['wireid']);
		} else {
			//
		}

		#echo '<HR>connlist:<PRE>';print_r($connlist);echo '</PRE>';
		$this->smarty->assign('wireid',$_GET['wireid']);
		$this->smarty->assign('connlist',$connlist);
		$this->smarty->display('netelements/connect.html');
	}

	function _error() {
                $layout['module'] = 'notfound';
                $layout['pagetitle'] = trans('Error!');
                $this->smarty->assign('layout', $layout);
                $this->smarty->assign('server', $_SERVER);
                $this->smarty->display('notfound.html');
	}
}
