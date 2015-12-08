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

    public function NetObjUpdate($data)
    {
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
            'ports' => $data['ports'],
            'purchasetime' => $data['purchasetime'],
            'guaranteeperiod' => $data['guaranteeperiod'],
            'shortname' => $data['shortname'],
            'nastype' => $data['nastype'],
            'clients' => $data['clients'],
            'secret' => $data['secret'],
            'community' => $data['community'],
            'channelid' => !empty($data['channelid']) ? $data['channelid'] : NULL,
            'longitude' => !empty($data['longitude']) ? str_replace(',', '.', $data['longitude']) : null,
            'latitude' => !empty($data['latitude']) ? str_replace(',', '.', $data['latitude']) : null,
            'invprojectid' => $data['invprojectid'],
            'netnodeid' => $data['netnodeid'],
            'status' => $data['status'],
            'netdevicemodelid' => !empty($data['netdevicemodelid']) ? $data['netdevicemodelid'] : null,
            $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $data['id'],
        );
        $res = $this->db->Execute('UPDATE netdevices SET name=?, description=?, producer=?, location=?,
				location_city=?, location_street=?, location_house=?, location_flat=?,
				model=?, serialnumber=?, ports=?, purchasetime=?, guaranteeperiod=?, shortname=?,
				nastype=?, clients=?, secret=?, community=?, channelid=?, longitude=?, latitude=?,
				invprojectid=?, netnodeid=?, status=?, netdevicemodelid=?
				WHERE id=?', array_values($args));
        if ($this->syslog && $res)
            $this->syslog->AddMessage(SYSLOG_RES_NETDEV, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV]));
    }

    public function NetObjAdd($data)
    {
        global $SYSLOG_RESOURCE_KEYS;
        return FALSE;

        $args = array(
            'name' => $data['name'],
            'location' => trim($data['location']),
            'location_city' => $data['location_city'] ? trim($data['location_city']) : null,
            'location_street' => $data['location_street'] ? trim($data['location_street']) : null,
            'location_house' => $data['location_house'] ? trim($data['location_house']) : null,
            'location_flat' => $data['location_flat'] ? trim($data['location_flat']) : null,
            'description' => $data['description'],
            'producer' => $data['producer'],
            'model' => $data['model'],
            'serialnumber' => $data['serialnumber'],
            'ports' => $data['ports'],
            'purchasetime' => $data['purchasetime'],
            'guaranteeperiod' => $data['guaranteeperiod'],
            'shortname' => $data['shortname'],
            'nastype' => $data['nastype'],
            'clients' => $data['clients'],
            'secret' => $data['secret'],
            'community' => $data['community'],
            'channelid' => !empty($data['channelid']) ? $data['channelid'] : NULL,
            'longitude' => !empty($data['longitude']) ? str_replace(',', '.', $data['longitude']) : NULL,
            'latitude' => !empty($data['latitude']) ? str_replace(',', '.', $data['latitude']) : NULL,
            'invprojectid' => $data['invprojectid'],
            'netnodeid' => $data['netnodeid'],
            'status' => $data['status'],
            'netdevicemodelid' => !empty($data['netdevicemodelid']) ? $data['netdevicemodelid'] : null,
        );
        if ($this->db->Execute('INSERT INTO netdevices (name, location,
				location_city, location_street, location_house, location_flat,
				description, producer, model, serialnumber,
				ports, purchasetime, guaranteeperiod, shortname,
				nastype, clients, secret, community, channelid,
				longitude, latitude, invprojectid, netnodeid, status, netdevicemodelid)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($args))) {
            $id = $this->db->GetLastInsertID('netdevices');

            // EtherWerX support (devices have some limits)
            // We must to replace big ID with smaller (first free)
            if ($id > 99999 && ConfigHelper::checkValue(ConfigHelper::getConfig('phpui.ewx_support', false))) {
                $this->db->BeginTrans();
                $this->db->LockTables('ewx_channels');

                if ($newid = $this->db->GetOne('SELECT n.id + 1 FROM ewx_channels n 
						LEFT OUTER JOIN ewx_channels n2 ON n.id + 1 = n2.id
						WHERE n2.id IS NULL AND n.id <= 99999
						ORDER BY n.id ASC LIMIT 1')) {
                    $this->db->Execute('UPDATE ewx_channels SET id = ? WHERE id = ?', array($newid, $id));
                    $id = $newid;
                }

                $this->db->UnLockTables();
                $this->db->CommitTrans();
            }

            if ($this->syslog) {
                $args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV]] = $id;
                $this->syslog->AddMessage(SYSLOG_RES_NETDEV, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV]));
            }
            return $id;
        } else
            return FALSE;
    }

    public function NetObjExists($id)
    {
        return ($this->db->GetOne('SELECT * FROM netdevices WHERE id=?', array($id)) ? TRUE : FALSE);
    }

    public function GetNetObjIDByNode($id)
    {
        return $this->db->GetOne('SELECT netdev FROM nodes WHERE id=?', array($id));
    }

    public function GetNetObjList($order = 'name,asc', $search = array())
    {
        list($order, $direction) = sscanf($order, '%[^,],%s');

        ($direction == 'desc') ? $direction = 'desc' : $direction = 'asc';

        switch ($order) {
            case 'id':
                $sqlord = ' ORDER BY id';
                break;
            case 'producer':
                $sqlord = ' ORDER BY producer';
                break;
            case 'model':
                $sqlord = ' ORDER BY model';
                break;
            case 'ports':
                $sqlord = ' ORDER BY ports';
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
					$where[] = 'd.status = ' . intval($value);
				break;
			case 'project':
				if ($value > 0)
					$where[] = '(d.invprojectid = ' . intval($value)
						. ' OR (d.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid = ' . intval($value) . '))';
				elseif ($value == -2)
					$where[] = '(d.invprojectid IS NULL OR (d.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid IS NULL))';
				break;
			case 'netnode':
				if ($value > 0)
					$where[] = 'd.netnodeid = ' . intval($value);
				elseif ($value == -2)
					$where[] = 'd.netnodeid IS NULL';
				break;
			case 'producer':
			case 'model':
				if (!preg_match('/^-[0-9]+$/', $value))
					$where[] = "UPPER(TRIM(d.$key)) = UPPER(" . $this->db->Escape($value) . ")";
				elseif ($value == -2)
					$where[] = "d.$key = ''";
				break;
		}

	$netdevlist = $this->db->GetAll('SELECT d.id, d.name, d.location,
			d.description, d.producer, d.model, d.serialnumber, d.ports,
			(SELECT COUNT(*) FROM nodes WHERE netdev=d.id AND ownerid > 0)
			+ (SELECT COUNT(*) FROM netlinks WHERE src = d.id OR dst = d.id)
			AS takenports, d.netnodeid, n.name AS netnode,
			lb.name AS borough_name, lb.type AS borough_type,
			ld.name AS district_name, ls.name AS state_name
			FROM netdevices d
			LEFT JOIN invprojects p ON p.id = d.invprojectid
			LEFT JOIN netnodes n ON n.id = d.netnodeid
			LEFT JOIN location_cities lc ON lc.id = d.location_city
			LEFT JOIN location_boroughs lb ON lb.id = lc.boroughid
			LEFT JOIN location_districts ld ON ld.id = lb.districtid
			LEFT JOIN location_states ls ON ls.id = ld.stateid '
			. (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
                . ($sqlord != '' ? $sqlord . ' ' . $direction : ''));

        $netdevlist['total'] = sizeof($netdevlist);
        $netdevlist['order'] = $order;
        $netdevlist['direction'] = $direction;

        return $netdevlist;
    }

    public function GetNetObjNames()
    {
        return $this->db->GetAll('SELECT id, name, location, producer 
			FROM netdevices ORDER BY name');
    }

    public function GetNetObj($id)
    {
        $result = $this->db->GetRow('SELECT d.*, t.name AS nastypename, c.name AS channel,
				(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netdevices d
			LEFT JOIN nastypes t ON (t.id = d.nastype)
			LEFT JOIN ewx_channels c ON (d.channelid = c.id)
			LEFT JOIN location_cities lc ON (lc.id = d.location_city)
			LEFT JOIN location_streets lst ON (lst.id = d.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE d.id = ?', array($id));

        $result['takenports'] = $this->CountNetObjLinks($id);
	$result['radiosectors'] = $this->db->GetAll('SELECT * FROM netradiosectors WHERE netdev = ? ORDER BY name', array($id));

        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); // transform to UNIX timestamp
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;

        return $result;
    }

    public function DeleteNetObj($id)
    {
        global $SYSLOG_RESOURCE_KEYS;

        $this->db->BeginTrans();
        if ($this->syslog) {
            $netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE src = ? OR dst = ?', array($id, $id));
            if (!empty($netlinks))
                foreach ($netlinks as $netlink) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $netlink['src'],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $netlink['dst'],
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
            $nodes = $this->db->GetCol('SELECT id FROM nodes WHERE ownerid = 0 AND netdev = ?', array($id));
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
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $id,
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
            $nodes = $this->db->GetAll('SELECT id, ownerid FROM nodes WHERE ownerid <> 0 AND netdev = ?', array($id));
            if (!empty($nodes))
                foreach ($nodes as $node) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node['id'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST] => $node['ownerid'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => 0,
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array_keys($args));
                }
            $args = array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETDEV] => $id);
            $this->syslog->AddMessage(SYSLOG_RES_NETDEV, SYSLOG_OPER_DELETE, $args, array_keys($args));
        }
        $this->db->Execute('DELETE FROM netlinks WHERE src=? OR dst=?', array($id, $id));
        $this->db->Execute('DELETE FROM nodes WHERE ownerid=0 AND netdev=?', array($id));
        $this->db->Execute('UPDATE nodes SET netdev=0 WHERE netdev=?', array($id));
        $this->db->Execute('DELETE FROM netdevices WHERE id=?', array($id));
        $this->db->CommitTrans();
    }

}
