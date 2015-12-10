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
		'description' => $data['description'],
		'producer' => $data['producer'],
		'location' => trim($data['location']),
		'location_city' => $data['location_city'] ? trim($data['location_city']) : null,
		'location_street' => $data['location_street'] ? trim($data['location_street']) : null,
		'location_house' => $data['location_house'] ? trim($data['location_house']) : null,
		'location_flat' => $data['location_flat'] ? trim($data['location_flat']) : null,
		'model' => $data['model'],
		'serialnumber' => $data['serialnumber'],
		'parameter' => $data['parameter'],
		'purchasetime' => $data['purchasetime'],
		'guaranteeperiod' => $data['guaranteeperiod'],
		'longitude' => !empty($data['longitude']) ? str_replace(',', '.', $data['longitude']) : null,
		'latitude' => !empty($data['latitude']) ? str_replace(',', '.', $data['latitude']) : null,
		'invprojectid' => $data['invprojectid'],
		'netnodeid' => $data['netnodeid'],
		'status' => $data['status'],
		$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETCAB] => $data['id'],
	);
	$res = $this->db->Execute('UPDATE netcables SET name=?, description=?, producer=?, location=?,
				location_city=?, location_street=?, location_house=?, location_flat=?,
				model=?, serialnumber=?, parameter=?, purchasetime=?, guaranteeperiod=?,
				longitude=?, latitude=?, invprojectid=?, netnodeid=?, status=?
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
	$result = $this->db->GetRow('SELECT c.*, 
			(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netcables c
			LEFT JOIN location_cities lc ON (lc.id = c.location_city)
			LEFT JOIN location_streets lst ON (lst.id = c.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE c.id = ?', array($id));

	#$result['takenports'] = $this->CountNetCabLinks($id);

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

}
