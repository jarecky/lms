<?php

/*
 *  LMS version 1.11-git
 *
 *  Copyright (C) 2001-2013 LMS Cabelopers
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
 * LMSNetCabManager
 *
 * @author Jaroslaw Dziubek <jaroslaw.dziubek@perfect.net.pl>
 */
class LMSNetCabManager extends LMSManager implements LMSNetCabManagerInterface
{

public function NetCabUpdate($data) {
	global $SYSLOG_RESOURCE_KEYS;

	$args = array(
                'name' => $data['name'],
                'fibers' => $data['fibers'],
                'length' => $data['length'],
                'begin' => $data['begin'],
                'end' => $data['end'],
                'description' => $data['description'],
                'producer' => $data['producer'],
                'model' => $data['model'],
                'purchasetime' => $data['purchasetime'],
                'guaranteeperiod' => $data['guaranteeperiod'],
                'status' => $data['status'],
                'invprojectid' => $data['invprojectid'],
		$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $data['id'],
	);
	$res = $this->db->Execute('UPDATE netcables SET name=?, fibers=?, length=?, 
				begin=?, end=?, description=?, producer=?, model=?, 
				purchasetime=?, guaranteeperiod=?, invprojectid=?, status=?
				WHERE id=?', array_values($args));
	if ($this->syslog && $res)
		$this->syslog->AddMessage(SYSLOG_RES_NETCAB, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB]));
}

public function NetCabAdd($data) {
	global $SYSLOG_RESOURCE_KEYS;

	$args = array(
		'name' => $data['name'],
		'fibers' => $data['fibers'],
		'length' => $data['length'],
		'begin' => $data['begin'],
		'end' => $data['end'],
		'description' => $data['description'],
		'producer' => $data['producer'],
		'model' => $data['model'],
		'purchasetime' => $data['purchasetime'],
		'guaranteeperiod' => $data['guaranteeperiod'],
		'status' => $data['status'],
		'invprojectid' => $data['invprojectid'],
	);

	if ($this->db->Execute('INSERT INTO netcables (name, fibers, length, begin, end,
				description, producer, model, purchasetime, 
				guaranteeperiod, status, invprojectid)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($args))) {
		$id = $this->db->GetLastInsertID('netcables');

		if ($this->syslog) {
			$args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB]] = $id;
			$this->syslog->AddMessage(SYSLOG_RES_NETCAB, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB]));
		}
		return $id;
	} else {
		#print_r($this->db->GetErrors());
		return FALSE;
	}
}

public function NetCabExists($id) {
	return ($this->db->GetOne('SELECT * FROM netcables WHERE id=?', array($id)) ? TRUE : FALSE);
}

public function GetNetCabIDByNode($id) {
	return $this->db->GetOne('SELECT netcab FROM nodes WHERE id=?', array($id));
}

public function GetNetCabList($order = 'name,asc', $search = array()) {
	global $LMS;
	list($order, $direction) = sscanf($order, '%[^,],%s');

	($direction == 'desc') ? $direction = 'desc' : $direction = 'asc';

	switch ($order) {
		case 'id':
			$sqlord = ' ORDER BY id';
			break;
		case 'type':
			$sqlord = ' ORDER BY c.type';
			break;
		case 'producer':
			$sqlord = ' ORDER BY producer';
			break;
		case 'model':
			$sqlord = ' ORDER BY model';
			break;
		default:
			$sqlord = ' ORDER BY name';
			break;
	}

	$where = array();
	foreach ($search as $key => $value)
		switch ($key) {
			case 'status':
				if ($value != -1)
					$where[] = 'c.status = ' . intval($value);
				break;
			case 'project':
				if ($value > 0)
					$where[] = '(c.invprojectid = ' . intval($value)
						. ' OR (c.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid = ' . intval($value) . '))';
				elseif ($value == -2)
					$where[] = '(c.invprojectid IS NULL OR (c.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid IS NULL))';
				break;
			case 'producer':
			case 'model':
				if (!preg_match('/^-[0-9]+$/', $value))
					$where[] = "UPPER(TRIM(c.$key)) = UPPER(" . $this->db->Escape($value) . ")";
				elseif ($value == -2)
					$where[] = "c.$key = ''";
				break;
		}

	$netcablist = $this->db->GetAll('SELECT c.id, c.name, 
			c.fibers, c.length, c.begin, c.end,
			c.description, c.producer, c.model  
			FROM netcables c
			LEFT JOIN invprojects p ON p.id = c.invprojectid '
			. (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
		. ($sqlord != '' ? $sqlord . ' ' . $direction : ''));

	foreach ($netcablist AS $id => $netcable) {
		if (isset($netcable['begin'])) 
			$netcablist[$id]['begin'] = $LMS->GetNetObj($netcable['begin']);
		if (isset($netcable['end'])) 
			$netcablist[$id]['end'] = $LMS->GetNetObj($netcable['end']);
	}
	$netcablist['total'] = sizeof($netcablist);
	$netcablist['order'] = $order;
	$netcablist['direction'] = $direction;

	#echo '<PRE>';print_r($netcablist);echo '</PRE>';
	return $netcablist;
}

public function GetNetCabNames() {
	return $this->db->GetAll('SELECT id, name, location, producer 
		FROM netcables ORDER BY name');
}

public function GetNetCab($id) {
	$result = $this->db->GetRow('SELECT * FROM netcables WHERE id = ?', array($id));

	if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
		$result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
		// transform to UNIX timestamp
	elseif ($result['guaranteeperiod'] == NULL)
		$result['guaranteeperiod'] = -1;

	return $result;
}

public function DeleteNetCab($id) {
	global $SYSLOG_RESOURCE_KEYS;

	$this->db->BeginTrans();
	if ($this->syslog) {
		$netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE src = ? OR dst = ?', array($id, $id));
		if (!empty($netlinks))
		foreach ($netlinks as $netlink) {
			$args = array(
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $netlink['src'],
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $netlink['dst'],
			);
			$this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
		}
		$nodes = $this->db->GetCol('SELECT id FROM nodes WHERE ownerid = 0 AND netcab = ?', array($id));
		if (!empty($nodes))
		foreach ($nodes as $node) {
			$macs = $this->db->GetCol('SELECT id FROM macs WHERE nodeid = ?', array($node));
			if (!empty($macs))
			foreach ($macs as $mac) {
				$args = array(
				$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_MAC] => $mac,
				$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node,
				);
				$this->syslog->AddMessage(SYSLOG_RES_MAC, SYSLOG_OPER_DELETE, $args, array_keys($args));
			}
			$args = array(
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node,
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $id,
			);
			$this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_DELETE, $args, array_keys($args));
		}
		$nodes = $this->db->GetAll('SELECT id, ownerid FROM nodes WHERE ownerid <> 0 AND netcab = ?', array($id));
		if (!empty($nodes))
		foreach ($nodes as $node) {
			$args = array(
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node['id'],
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST] => $node['ownerid'],
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => 0,
			);
			$this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array_keys($args));
		}
		$args = array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $id);
		$this->syslog->AddMessage(SYSLOG_RES_NETCAB, SYSLOG_OPER_DELETE, $args, array_keys($args));
	}
	$this->db->Execute('DELETE FROM netlinks WHERE src=? OR dst=?', array($id, $id));
	$this->db->Execute('DELETE FROM nodes WHERE ownerid=0 AND netcab=?', array($id));
	$this->db->Execute('UPDATE nodes SET netcab=0 WHERE netcab=?', array($id));
	$this->db->Execute('DELETE FROM netcables WHERE id=?', array($id));
	$this->db->CommitTrans();
}

public function GetNetCabInObj($id) {
	$result=$this->db->GetAll('SELECT * FROM netcables WHERE begin=? OR end=?',array($id, $id));

	foreach ($result AS $id => $cable) {
		$splice=array();
		for ($tube=1;$tube<=ceil($cable['fibers']/12);$tube++) {
			for ($fiber=1;$fiber<=12;$fiber++) {
				if ((($tube-1)*12+$fiber)<=$cable['fibers'])
					$splice[$tube][$fiber]=-1;
			}
		}
		$splices=$this->db->GetAll('SELECT * FROM netsplices WHERE objecid=? AND cableid=?',array($id,$result['id']));
	
		$result[$id]['splices']=$splice;
	}

	echo '<PRE>';print_r($result);echo '</PRE>';

	return $result;
}

}

?>
