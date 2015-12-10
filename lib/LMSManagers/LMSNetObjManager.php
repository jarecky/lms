<?php

/*
 *  LMS version 1.11-git
 *
 *  Copyright (C) 2001-2013 LMS Objelopers
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
 * LMSNetObjManager
 *
 * @author Jaroslaw Dziubek <jaroslaw.dziubek@perfect.net.pl>
 */
class LMSNetObjManager extends LMSManager implements LMSNetObjManagerInterface
{

public function NetObjUpdate($data) {
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
		$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => $data['id'],
	);
	$res = $this->db->Execute('UPDATE netobjects SET name=?, description=?, producer=?, location=?,
				location_city=?, location_street=?, location_house=?, location_flat=?,
				model=?, serialnumber=?, parameter=?, purchasetime=?, guaranteeperiod=?,
				longitude=?, latitude=?, invprojectid=?, netnodeid=?, status=?
				WHERE id=?', array_values($args));
	if ($this->syslog && $res)
		$this->syslog->AddMessage(SYSLOG_RES_NETOBJ, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ]));
}

public function NetObjAdd($data) {
	global $SYSLOG_RESOURCE_KEYS;

	$args = array(
		'name' => $data['name'],
		'type' => $data['type'],
		'location' => trim($data['location']),
		'location_city' => $data['location_city'] ? trim($data['location_city']) : null,
		'location_street' => $data['location_street'] ? trim($data['location_street']) : null,
		'location_house' => $data['location_house'] ? trim($data['location_house']) : null,
		'location_flat' => $data['location_flat'] ? trim($data['location_flat']) : null,
		'description' => $data['description'],
		'producer' => $data['producer'],
		'model' => $data['model'],
		'serialnumber' => $data['serialnumber'],
		'parameter' => $data['parameter'],
		'purchasetime' => $data['purchasetime'],
		'guaranteeperiod' => $data['guaranteeperiod'],
		'longitude' => !empty($data['longitude']) ? str_replace(',', '.', $data['longitude']) : NULL,
		'latitude' => !empty($data['latitude']) ? str_replace(',', '.', $data['latitude']) : NULL,
		'status' => $data['status'],
		'invprojectid' => $data['invprojectid'],
		'netnodeid' => $data['netnodeid']
	);

	if ($this->db->Execute('INSERT INTO netobjects (name, type, location,
				location_city, location_street, location_house, location_flat,
				description, producer, model, serialnumber,
				parameter, purchasetime, guaranteeperiod, status,
				longitude, latitude, invprojectid, netnodeid)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($args))) {
		$id = $this->db->GetLastInsertID('netobjects');

		if ($this->syslog) {
			$args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ]] = $id;
			$this->syslog->AddMessage(SYSLOG_RES_NETOBJ, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ]));
		}
		return $id;
	} else {
		return FALSE;
	}
}

public function NetObjExists($id) {
	return ($this->db->GetOne('SELECT * FROM netobjects WHERE id=?', array($id)) ? TRUE : FALSE);
}

public function GetNetObjIDByNode($id) {
	return $this->db->GetOne('SELECT netobj FROM nodes WHERE id=?', array($id));
}

public function GetNetObjList($order = 'name,asc', $search = array()) {
	list($order, $direction) = sscanf($order, '%[^,],%s');

	($direction == 'desc') ? $direction = 'desc' : $direction = 'asc';

	switch ($order) {
		case 'id':
			$sqlord = ' ORDER BY id';
			break;
		case 'type':
			$sqlord = ' ORDER BY o.type';
			break;
		case 'producer':
			$sqlord = ' ORDER BY producer';
			break;
		case 'model':
			$sqlord = ' ORDER BY model';
			break;
		case 'parameter':
			$sqlord = ' ORDER BY parameter';
			break;
		case 'takenports':
			$sqlord = ' ORDER BY takenports';
			break;
		case 'serialnumber':
			$sqlord = ' ORDER BY serialnumber';
			break;
		case 'location':
			$sqlord = ' ORDER BY location';
			break;
		case 'netnode':
			$sqlord = ' ORDER BY netnode';
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
					$where[] = 'o.status = ' . intval($value);
				break;
			case 'project':
				if ($value > 0)
					$where[] = '(o.invprojectid = ' . intval($value)
						. ' OR (o.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid = ' . intval($value) . '))';
				elseif ($value == -2)
					$where[] = '(o.invprojectid IS NULL OR (o.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid IS NULL))';
				break;
			case 'netnode':
				if ($value > 0)
					$where[] = 'o.netnodeid = ' . intval($value);
				elseif ($value == -2)
					$where[] = 'o.netnodeid IS NULL';
				break;
			case 'type':
				if ($value > 0)
					$where[] = 'o.type = ' . intval($value);
				break;
			case 'producer':
			case 'model':
				if (!preg_match('/^-[0-9]+$/', $value))
					$where[] = "UPPER(TRIM(o.$key)) = UPPER(" . $this->db->Escape($value) . ")";
				elseif ($value == -2)
					$where[] = "o.$key = ''";
				break;
		}

	$netobjlist = $this->db->GetAll('SELECT o.id, o.name, o.type, o.location,
			o.description, o.producer, o.model, o.serialnumber, 
			o.parameter, o.netnodeid, n.name AS netnode,
			lb.name AS borough_name, lb.type AS borough_type,
			ld.name AS district_name, ls.name AS state_name
			FROM netobjects o
			LEFT JOIN invprojects p ON p.id = o.invprojectid
			LEFT JOIN netnodes n ON n.id = o.netnodeid
			LEFT JOIN location_cities lc ON lc.id = o.location_city
			LEFT JOIN location_boroughs lb ON lb.id = lc.boroughid
			LEFT JOIN location_districts ld ON ld.id = lb.districtid
			LEFT JOIN location_states ls ON ls.id = ld.stateid '
			. (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
		. ($sqlord != '' ? $sqlord . ' ' . $direction : ''));

	$netobjlist['total'] = sizeof($netobjlist);
	$netobjlist['order'] = $order;
	$netobjlist['direction'] = $direction;

	return $netobjlist;
}

public function GetNetObjNames() {
	return $this->db->GetAll('SELECT id, name, location, producer 
		FROM netobjects ORDER BY name');
}

public function GetNetObj($id) {
	$result = $this->db->GetRow('SELECT o.*, 
			(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netobjects o
			LEFT JOIN location_cities lc ON (lc.id = o.location_city)
			LEFT JOIN location_streets lst ON (lst.id = o.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE o.id = ?', array($id));

	#$result['takenports'] = $this->CountNetObjLinks($id);

	if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
		$result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
		// transform to UNIX timestamp
	elseif ($result['guaranteeperiod'] == NULL)
		$result['guaranteeperiod'] = -1;

	return $result;
}

public function DeleteNetObj($id) {
	global $SYSLOG_RESOURCE_KEYS;

	$this->db->BeginTrans();
	if ($this->syslog) {
		$netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE src = ? OR dst = ?', array($id, $id));
		if (!empty($netlinks))
		foreach ($netlinks as $netlink) {
			$args = array(
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => $netlink['src'],
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => $netlink['dst'],
			);
			$this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
		}
		$nodes = $this->db->GetCol('SELECT id FROM nodes WHERE ownerid = 0 AND netobj = ?', array($id));
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
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => $id,
			);
			$this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_DELETE, $args, array_keys($args));
		}
		$nodes = $this->db->GetAll('SELECT id, ownerid FROM nodes WHERE ownerid <> 0 AND netobj = ?', array($id));
		if (!empty($nodes))
		foreach ($nodes as $node) {
			$args = array(
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node['id'],
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST] => $node['ownerid'],
			$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => 0,
			);
			$this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array_keys($args));
		}
		$args = array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETOBJ] => $id);
		$this->syslog->AddMessage(SYSLOG_RES_NETOBJ, SYSLOG_OPER_DELETE, $args, array_keys($args));
	}
	$this->db->Execute('DELETE FROM netlinks WHERE src=? OR dst=?', array($id, $id));
	$this->db->Execute('DELETE FROM nodes WHERE ownerid=0 AND netobj=?', array($id));
	$this->db->Execute('UPDATE nodes SET netobj=0 WHERE netobj=?', array($id));
	$this->db->Execute('DELETE FROM netobjects WHERE id=?', array($id));
	$this->db->CommitTrans();
}

}
