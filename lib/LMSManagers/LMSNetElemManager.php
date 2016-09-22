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
 * LMSNetElemManager
 *
 * @author Maciej Lew <maciej.lew.1987@gmail.com>
 */
class LMSNetElemManager extends LMSManager implements LMSNetElemManagerInterface
{

    public function GetNetElemLinkedNodes($id)
    {
        return $this->db->GetAll('SELECT n.id AS id, n.name AS name, linktype, rs.name AS radiosector,
        		linktechnology, linkspeed,
			ipaddr, inet_ntoa(ipaddr) AS ip, ipaddr_pub, inet_ntoa(ipaddr_pub) AS ip_pub, 
			n.netdev, port, ownerid,
			' . $this->db->Concat('c.lastname', "' '", 'c.name') . ' AS owner,
			net.name AS netname, n.location,
			lc.name AS city_name, lb.name AS borough_name, lb.type AS borough_type,
			ld.name AS district_name, ls.name AS state_name
			FROM vnodes n
			JOIN customerview c ON c.id = ownerid
			JOIN networks net ON net.id = n.netid
			LEFT JOIN netradiosectors rs ON rs.id = n.linkradiosector
			LEFT JOIN location_cities lc ON lc.id = n.location_city
			LEFT JOIN location_boroughs lb ON lb.id = lc.boroughid
			LEFT JOIN location_districts ld ON ld.id = lb.districtid
			LEFT JOIN location_states ls ON ls.id = ld.stateid
			WHERE n.netdev = ? AND ownerid > 0 
			ORDER BY n.name ASC', array($id));
    }

    public function NetElemLinkNode($id, $devid, $link = NULL)
    {
        global $SYSLOG_RESOURCE_KEYS;

	if (empty($link)) {
		$type = 0;
		$technology = 0;
		$radiosector = NULL;
		$speed = 100000;
		$port = 0;
	} else {
		$type = isset($link['type']) ? intval($link['type']) : 0;
		$radiosector = isset($link['radiosector']) ? intval($link['radiosector']) : NULL;
		$technology = isset($link['technology']) ? intval($link['technology']) : 0;
		$speed = isset($link['speed']) ? intval($link['speed']) : 100000;
		$port = isset($link['port']) ? intval($link['port']) : 0;
	}

        $args = array(
            $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $devid,
            'linktype' => $type,
            'linkradiosector' => $radiosector,
            'linktechnology' => $technology,
            'linkspeed' => $speed,
            'port' => intval($port),
            $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $id,
        );
        $res = $this->db->Execute('UPDATE nodes SET netdev=?, linktype=?, linkradiosector=?,
			linktechnology=?, linkspeed=?, port=?
			WHERE id=?', array_values($args));
        if ($this->syslog && $res) {
            $args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST]] = $this->db->GetOne('SELECT ownerid FROM vnodes WHERE id=?', array($id));
            $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE],
                $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM],
                $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST]));
        }
        return $res;
    }

    public function SetNetElemLinkType($dev1, $dev2, $link = NULL)
    {
	global $SYSLOG_RESOURCE_KEYS;

	if (empty($link)) {
		$type = 0;
		$srcradiosector = null;
		$dstradiosector = null;
		$technology = 0;
		$speed = 100000;
	} else {
		$type = isset($link['type']) ? $link['type'] : 0;
		$srcradiosector = isset($link['srcradiosector']) ? (intval($link['srcradiosector']) ? intval($link['srcradiosector']) : null) : null;
		$dstradiosector = isset($link['dstradiosector']) ? (intval($link['dstradiosector']) ? intval($link['dstradiosector']) : null) : null;
		$technology = isset($link['technology']) ? $link['technology'] : 0;
		$speed = isset($link['speed']) ? $link['speed'] : 100000;
	}

	$args = array(
		'type' => $type,
		'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $srcradiosector,
		'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $dstradiosector,
		'technology' => $technology,
		'speed' => $speed,
		'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev2,
		'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev1,
	);
	$res = $this->db->Execute('UPDATE netlinks SET type=?, srcradiosector=?, dstradiosector=?, technology=?, speed=?
		WHERE src=? AND dst=?', array_values($args));
	if (!$res) {
		$args = array(
			'type' => $type,
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $srcradiosector,
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $dstradiosector,
			'technology' => $technology,
			'speed' => $speed,
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev1,
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev2,
		);
		$res = $this->db->Execute('UPDATE netlinks SET type=?, dstradiosector=?, srcradiosector=?, technology=?, speed=?
			WHERE src=? AND dst=?', array_values($args));
	}
	if ($this->syslog && $res) {
		$args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK]] =
			$this->db->GetOne('SELECT id FROM netlinks WHERE (src=? AND dst=?) OR (dst=? AND src=?)', array($dev1, $dev2, $dev1, $dev2));
		$this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK],
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM],
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM],
			'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR],
			'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR],
		));
	}
	return $res;
    }

    public function IsNetElemLink($dev1, $dev2)
    {
        return $this->db->GetOne('SELECT COUNT(id) FROM netlinks 
			WHERE (src=? AND dst=?) OR (dst=? AND src=?)', array($dev1, $dev2, $dev1, $dev2));
    }

    public function NetElemLink($dev1, $dev2, $link)
    {
        global $SYSLOG_RESOURCE_KEYS;

	$type = $link['type'];
	$srcradiosector = ($type == 1 ?
		(isset($link['srcradiosector']) && intval($link['srcradiosector']) ? intval($link['srcradiosector']) : null) : null);
	$dstradiosector = ($type == 1 ?
		(isset($link['dstradiosector']) && intval($link['dstradiosector']) ? intval($link['dstradiosector']) : null) : null);
	$technology = $link['technology'];
	$speed = $link['speed'];
	$sport = $link['srcport'];
	$dport = $link['dstport'];

        if ($dev1 != $dev2)
            if (!$this->IsNetElemLink($dev1, $dev2)) {
                $args = array(
                    'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev1,
                    'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $dev2,
                    'type' => $type,
                    'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $srcradiosector,
                    'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR] => $dstradiosector,
                    'technology' => $technology,
                    'speed' => $speed,
                    'srcport' => intval($sport),
                    'dstport' => intval($dport),
                );
                $res = $this->db->Execute('INSERT INTO netlinks 
					(src, dst, type, srcradiosector, dstradiosector, technology, speed, srcport, dstport) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', array_values($args));
                if ($this->syslog && $res) {
                    $args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK]] = $this->db->GetLastInsertID('netlinks');
                    $this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_RADIOSECTOR]));
                }
                return $res;
            }

        return FALSE;
    }

    public function NetElemUnLink($dev1, $dev2)
    {
        global $SYSLOG_RESOURCE_KEYS;

        if ($this->syslog) {
            $netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE (src=? AND dst=?) OR (dst=? AND src=?)', array($dev1, $dev2, $dev1, $dev2));
            if (!empty($netlinks))
                foreach ($netlinks as $netlink) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['src'],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['dst'],
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
        }
        $this->db->Execute('DELETE FROM netlinks WHERE (src=? AND dst=?) OR (dst=? AND src=?)', array($dev1, $dev2, $dev1, $dev2));
    }

    public function NetElemUpdate($data)
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
            $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $data['id'],
        );
        $res = $this->db->Execute('UPDATE netdevices SET name=?, description=?, producer=?, location=?,
				location_city=?, location_street=?, location_house=?, location_flat=?,
				model=?, serialnumber=?, ports=?, purchasetime=?, guaranteeperiod=?, shortname=?,
				nastype=?, clients=?, secret=?, community=?, channelid=?, longitude=?, latitude=?,
				invprojectid=?, netnodeid=?, status=?, netdevicemodelid=?
				WHERE id=?', array_values($args));
        if ($this->syslog && $res)
            $this->syslog->AddMessage(SYSLOG_RES_NETELEM, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]));
    }

    public function NetElemAdd($data)
    {
        global $SYSLOG_RESOURCE_KEYS;
        $args = array(
	    'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'],
            'producer' => $data['producer'] ? $data['producer'] : '',
            'model' => $data['model'] ? $data['model'] : '',
            'serialnumber' => $data['serialnumber'],
            'purchasetime' => $data['purchasetime'],
            'guaranteeperiod' => $data['guaranteeperiod'],
            'netnodeid' => $data['netnodeid'],
            'invprojectid' => $data['invprojectid'],
            'netdevicemodelid' => !empty($data['netdevicemodelid']) ? $data['netdevicemodelid'] : null,
            'status' => $data['status'],
        );
        if ($this->db->Execute('INSERT INTO netelements (name, type,
				description, producer, model, serialnumber,
				purchasetime, guaranteeperiod, netnodeid,
				invprojectid, netdevicemodelid, status)
				VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', array_values($args))) {
            $id = $this->db->GetLastInsertID('netelements');

            if ($this->syslog) {
                $args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]] = $id;
                $this->syslog->AddMessage(SYSLOG_RES_NETELEM, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]));
            }
            return $id;
        } else
            return FALSE;
    }

    public function NetElemAddActive($netelemdata,$netactivedata) {
        global $SYSLOG_RESOURCE_KEYS;
        $netelemid=$this->NetElemAdd($netelemdata);
	if ($netelemid) {
            // EtherWerX support (devices have some limits)
            // We must to replace big ID with smaller (first free)
            if ($netelemid > 99999 && ConfigHelper::checkValue(ConfigHelper::getConfig('phpui.ewx_support', false))) {
                $this->db->BeginTrans();
                $this->db->LockTables('ewx_channels');
                if ($newid = $this->db->GetOne('SELECT n.id + 1 FROM ewx_channels n
                                                LEFT OUTER JOIN ewx_channels n2 ON n.id + 1 = n2.id
                                                WHERE n2.id IS NULL AND n.id <= 99999
                                                ORDER BY n.id ASC LIMIT 1')) {
                    $this->db->Execute('UPDATE ewx_channels SET id = ? WHERE id = ?', array($newid, $netelemid));
                    $netelemid = $newid;
                }
                $this->db->UnLockTables();
                $this->db->CommitTrans();
            }
	    $args=array(
		'netelemid' => $netelemid,
            	'shortname' => $netactivedata['shortname'],
            	'nastype' => $netactivedata['nastype'],
            	'clients' => $netactivedata['clients'],
		'user' => '',
            	'secret' => $netactivedata['secret'],
            	'community' => $netactivedata['community'],
            	'channelid' => !empty($netactivedata['channelid']) ? $netactivedata['channelid'] : NULL,
	    );	    
	    if ($this->db->Execute("INSERT INTO netdevices (netelemid,shortname,nastype,clients,user,secret,community,channelid)
		    		VALUES (?,?,?,?,?,?,?,?)",array_values($args))) {
		$netactiveid = $this->db->GetLastInsertID('netdevices');
                if ($this->syslog) {
                    $args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]] = $id;
                    $this->syslog->AddMessage(SYSLOG_RES_NETELEM, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]));
                }
                $data=array(
                    'netelemid'           => $netelemid,
                    'type'                => 0,
                    'label'               => '',
                    'connectortype'       => $netsplitterdata['connector'] ? $netsplitterdata['connector'] : '0',
                    'technology'          => NULL,
                    'capacity'            => 1
                );
		foreach ($netactivedata['ports'] AS $port) {
		    $data['type']=$port['netporttype'];
                    $data['label']=$port['label'];
		    $data['connectortype']=$port['netconnector'];
		    $data['technology']=$port['nettechnology'];
		    $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity)
					VALUES (?,?,?,?,?,?)",array_values($data));

		}
		return($netelemid);
	    }
	}
	return FALSE;
    }	    

    public function NetElemAddPassive($netelemdata,$netpassivedata) {
	global $SYSLOG_RESOURCE_KEYS, $NETCONNECTORS;
        $netelemid=$this->NetElemAdd($netelemdata);
	if ($netelemid) {
           $data=array(
                'netelemid'           => $netelemid,
                'type'                => 0,
                'label'               => '',
                'connectortype'       => 0,
                'technology'          => 0,
                'capacity'            => 0
            );
            foreach ($netpassivedata['ports'] AS $port) {
                $data['type']=$port['netporttype'];
                $data['label']=$port['label'];
                $data['connectortype']=$port['netconnector'];
		$connid=$data['connectortype'];
		if ($connid<50) {
			if (preg_match('/[0-9]P([0-9])C/',$NETCONNECTORS[$connid],$conn)) {
				$data['capacity']=$conn[1]*2;
			} else {
				$data['capacity']=2;
			}
		} elseif ($connid<100) {
			$data['capacity']=2;
		} elseif ($connid<300) {
			$data['capacity']=2;
		} else {
			$data['capacity']=12;
		}
		echo '<PRE>';print_r($data);echo '</PRE>';
                $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity)
                                    VALUES (?,?,?,?,?,?)",array_values($data));
            }
            return($netelemid);
	}
	return FALSE;
    }	    

    public function NetElemAddCable($netelemdata,$netcabledata) {
        global $SYSLOG_RESOURCE_KEYS;
        $netelemid=$this->NetElemAdd($netelemdata);
	if ($netelemid) {
          $args=array(
          	'netelemid'	=> $netelemid,
		'type'		=> $netcabledata['type'],
		'label'		=> $netcabledata['label'],
		'capacity'	=> $netcabledata['capacity'],
		'distance'	=> $netcabledata['distance'],
		'colorschemaid'	=> $netcabledata['colorschemaid'],
		'dstnodeid'	=> $netcabledata['dstnodeid'],
          );
          if ($this->db->Execute("INSERT INTO netcables (netelemid, type,
		label,capacity,distance,colorschemaid,dstnodeid)
		VALUES (?,?,?,?,?,?,?)",array_values($args))) {

              $cableid = $this->db->GetLastInsertID('netcables');
	      if ($this->syslog) {
	  	$args[$SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]] = $cableid;
		$this->syslog->AddMessage(SYSLOG_RES_NETELEM, SYSLOG_OPER_ADD, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]));
              }
	      $data=array(
		  'netcableid'	=> $netelemid,
		  'type'	=> $netcabledata['wiretype'],
		  'bundle'	=> 1,
		  'wire'	=> 1,
              );
	      for ($i=0;$i<$netcabledata['capacity'];$i++) {
		 if ($netcabledata['type']==201) {
		   $data['wire'] = $i % 12 + 1;
		   $data['bundle'] = ceil(($i+1)/12);
                 } else {
		   $data['wire'] = $i + 1;
		   $data['bundle'] = 1;
		 }
		 $this->db->Execute("INSERT INTO netwires (netcableid,type,bundle,wire) 
			VALUES (?,?,?,?)",array_values($data));
                 echo '<PRE>';print_r($this->db->GetErrors());echo '</PRE>';
              }
	      return($netelemid);
	  }
	}

	return FALSE;
    }

    public function NetElemAddSplitter($netelemdata,$netsplitterdata) {
        global $SYSLOG_RESOURCE_KEYS;
        $netelemid=$this->NetElemAdd($netelemdata);
	if ($netelemid) {
	      $data=array(
		  'netelemid'		=> $netelemid,
		  'type'		=> 201,
		  'label'		=> '',
		  'connectortype'	=> $netsplitterdata['connector'] ? $netsplitterdata['connector'] : '0',
		  'technology'		=> 209,
                  'capacity'		=> 1
              );
	      for ($i=1;$i<=$netsplitterdata['in'];$i++) {
		 $data['label']='in'.$i;
		 $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity) 
			VALUES (?,?,?,?,?,?)",array_values($data));
              }
	      $data['type']=202;
              for ($i=1;$i<=$netsplitterdata['out'];$i++) {
                 $data['label']='out'.$i;
                 $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity)
                        VALUES (?,?,?,?,?,?)",array_values($data));
              }	      
	      return($netelemid);
	}
	return FALSE;

    }

    public function NetElemAddMultiplexer($netelemdata,$netmultiplexerdata) {
        global $SYSLOG_RESOURCE_KEYS;
        $netelemid=$this->NetElemAdd($netelemdata);
        if ($netelemid) {
              $data=array(
                  'netelemid'           => $netelemid,
                  'type'                => 201,
                  'label'               => '',
                  'connectortype'       => $netmultiplexerdata['connector'] ? $netmultiplexerdata['connector'] : '0',
                  'technology'          => 0,
                  'capacity'            => 1
              );
              for ($i=1;$i<=$netmultiplexerdata['in'];$i++) {
                 $data['label']='in'.$i;
                 $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity)
                        VALUES (?,?,?,?,?,?)",array_values($data));
              }
              $data['type']=202;
              for ($i=1;$i<=$netmultiplexerdata['out'];$i++) {
                 $data['label']='out'.$i;
                 $this->db->Execute("INSERT INTO netports (netelemid,type,label,connectortype,technology,capacity)
                        VALUES (?,?,?,?,?,?)",array_values($data));
              }
              return($netelemid);
        }
        return FALSE;
    }

    public function NetElemAddComputer($netelemdata,$netcomputerdata) {

    }
    
    public function GetNetElemType($id)
    {
        return ($this->db->GetOne("SELECT type FROM netelements WHERE id=?", array($id)));
    }

    public function NetElemExists($id)
    {
        return ($this->db->GetOne('SELECT * FROM netelements WHERE id=?', array($id)) ? TRUE : FALSE);
    }

    public function GetNetElemIDByNode($id)
    {
        return $this->db->GetOne('SELECT netelements FROM vnodes WHERE id=?', array($id));
    }

    public function CountNetElemLinks($id)
    {
        return $this->db->GetOne('SELECT COUNT(*) FROM netlinks WHERE src = ? OR dst = ?', array($id, $id)) + $this->db->GetOne('SELECT COUNT(*) FROM vnodes WHERE netdev = ? AND ownerid > 0', array($id));
    }

    public function GetNetElemLinkType($dev1, $dev2)
    {
        return $this->db->GetRow('SELECT type, technology, speed FROM netlinks
			WHERE (src=? AND dst=?) OR (dst=? AND src=?)',
			array($dev1, $dev2, $dev1, $dev2));
    }

    public function GetNetElemConnectedNames($id)
    {
        return $this->db->GetAll('SELECT d.id, d.name, d.description,
			d.location, d.producer, d.ports, l.type AS linktype,
			l.technology AS linktechnology, l.speed AS linkspeed, l.srcport, l.dstport,
			srcrs.name AS srcradiosector, dstrs.name AS dstradiosector,
			(SELECT COUNT(*) FROM netlinks WHERE src = d.id OR dst = d.id) 
			+ (SELECT COUNT(*) FROM vnodes WHERE netdev = d.id AND ownerid > 0)
			AS takenports,
			lc.name AS city_name, lb.name AS borough_name, lb.type AS borough_type,
			ld.name AS district_name, ls.name AS state_name
			FROM netdevices d
			JOIN (SELECT DISTINCT type, technology, speed, 
				(CASE src WHEN ? THEN dst ELSE src END) AS dev, 
				(CASE src WHEN ? THEN dstport ELSE srcport END) AS srcport, 
				(CASE src WHEN ? THEN srcport ELSE dstport END) AS dstport, 
				(CASE src WHEN ? THEN dstradiosector ELSE srcradiosector END) AS srcradiosector,
				(CASE src WHEN ? THEN srcradiosector ELSE dstradiosector END) AS dstradiosector
				FROM netlinks WHERE src = ? OR dst = ?
			) l ON (d.id = l.dev)
			LEFT JOIN location_cities lc ON lc.id = d.location_city
			LEFT JOIN location_boroughs lb ON lb.id = lc.boroughid
			LEFT JOIN location_districts ld ON ld.id = lb.districtid
			LEFT JOIN location_states ls ON ls.id = ld.stateid
			LEFT JOIN netradiosectors srcrs ON srcrs.id = l.srcradiosector
			LEFT JOIN netradiosectors dstrs ON dstrs.id = l.dstradiosector
			ORDER BY name', array($id, $id, $id, $id, $id, $id, $id));
    }

    public function GetNetElemList($order = 'name,asc', $search = array())
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
			case 'type':
				if ($value != -1)
					$where[] = 'e.type = '. intval($value);
				break;
			case 'status':
				if ($value != -1)
					$where[] = 'e.status = ' . intval($value);
				break;
			case 'project':
				if ($value > 0)
					$where[] = '(e.invprojectid = ' . intval($value)
						. ' OR (e.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid = ' . intval($value) . '))';
				elseif ($value == -2)
					$where[] = '(e.invprojectid IS NULL OR (e.invprojectid = ' . INV_PROJECT_SYSTEM . ' AND n.invprojectid IS NULL))';
				break;
			case 'netnode':
				if ($value > 0)
					$where[] = 'e.netnodeid = ' . intval($value);
				elseif ($value == -2)
					$where[] = 'e.netnodeid IS NULL';
				break;
			case 'producer':
			case 'model':
				if (!preg_match('/^-[0-9]+$/', $value))
					$where[] = "UPPER(TRIM(e.$key)) = UPPER(" . $this->db->Escape($value) . ")";
				elseif ($value == -2)
					$where[] = "e.$key = ''";
				break;
		}

	$netelemlist = $this->db->GetAll('SELECT e.type, e.id, e.name, n.location,
			e.description, e.producer, e.model, e.serialnumber,
			(SELECT COUNT(*) FROM netports WHERE netelemid = e.id) AS ports,
			(SELECT COUNT(*) FROM netlinks LEFT JOIN netports n ON (srcport = n.id OR dstport = n.id) WHERE n.netelemid = e.id) AS takenports, 
			e.netnodeid, n.name AS netnode,
			lb.name AS borough_name, lb.type AS borough_type,
			ld.name AS district_name, ls.name AS state_name
			FROM netelements e
			LEFT JOIN invprojects p ON p.id = e.invprojectid
			LEFT JOIN netnodes n ON n.id = e.netnodeid
			LEFT JOIN location_cities lc ON lc.id = n.location_city
			LEFT JOIN location_boroughs lb ON lb.id = lc.boroughid
			LEFT JOIN location_districts ld ON ld.id = lb.districtid
			LEFT JOIN location_states ls ON ls.id = ld.stateid '
			. (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
                . ($sqlord != '' ? $sqlord . ' ' . $direction : ''));

        $netelemlist['total'] = sizeof($netelemlist);
        $netelemlist['order'] = $order;
        $netelemlist['direction'] = $direction;
        return $netelemlist;
    }

    public function GetNetElemNames()
    {
        return $this->db->GetAll('SELECT id, name, location, producer 
			FROM netdevices ORDER BY name');
    }

    public function GetNotConnectedElements($id)
    {
        return $this->db->GetAll('SELECT d.id, d.name, d.description,
			d.location, d.producer, d.ports
			FROM netdevices d
			LEFT JOIN (SELECT DISTINCT 
				(CASE src WHEN ? THEN dst ELSE src END) AS dev 
				FROM netlinks WHERE src = ? OR dst = ?
			) l ON (d.id = l.dev)
			WHERE l.dev IS NULL AND d.id != ?
			ORDER BY name', array($id, $id, $id, $id));
    }

    public function GetNetElemActive($id)
    {
        $result = $this->db->GetRow('SELECT e.*, d.*, t.name AS nastypename, c.name AS channel,
				n.name AS nodename,
				(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				(SELECT COUNT(*) FROM netports WHERE netelemid=e.id) AS ports,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netelements e
			LEFT JOIN netnodes n ON (n.id = e.netnodeid)
			LEFT JOIN netdevices d ON (d.netelemid = e.id) 
			LEFT JOIN nastypes t ON (t.id = d.nastype)
			LEFT JOIN ewx_channels c ON (d.channelid = c.id)
			LEFT JOIN location_cities lc ON (lc.id = n.location_city)
			LEFT JOIN location_streets lst ON (lst.id = n.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE e.id = ?', array($id));
        $result['takenports'] = $this->CountNetElemLinks($id);
	$result['radiosectors'] = $this->db->GetAll('SELECT * FROM netradiosectors WHERE netdev = ? ORDER BY name', array($id));

        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;
        $result['projectname'] = trans('none');
        if ($result['invprojectid']) {
            $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($result['invprojectid']));
            if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                    /* inherited */
                    if ($netnode) {
                        $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",array($netnode['invprojectid']));
                            if ($prj)
                                $result['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                    }
                } else
                    $result['projectname'] = $prj['name'];
            }
        }
        return $result;
    }

    public function GetNetElemPassive($id)
    {
        $result = $this->db->GetRow('SELECT e.*, n.name AS nodename,
                                (CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
                                (SELECT COUNT(*) FROM netports WHERE netelemid=e.id) AS ports,
                                lt.name AS street_type,
                                lc.name AS city_name,
                                lb.name AS borough_name, lb.type AS borough_type,
                                ld.name AS district_name, ls.name AS state_name
                        FROM netelements e
                        LEFT JOIN netnodes n ON (n.id = e.netnodeid)
                        LEFT JOIN location_cities lc ON (lc.id = n.location_city)
                        LEFT JOIN location_streets lst ON (lst.id = n.location_street)
                        LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
                        LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
                        LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
                        LEFT JOIN location_states ls ON (ls.id = ld.stateid)
                        WHERE e.id = ?', array($id));
        #$result['takenports'] = $this->CountNetElemLinks($id);

        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;
        $result['projectname'] = trans('none');
        if ($result['invprojectid']) {
            $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($result['invprojectid']));
            if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                    /* inherited */
                    if ($netnode) {
                        $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",array($netnode['invprojectid']));
                            if ($prj)
                                $result['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                    }
                } else
                    $result['projectname'] = $prj['name'];
            }
        }
        return $result;

    }

    public function GetNetElemCable($id)
    {
        $result = $this->db->GetRow('SELECT e.*, e.netnodeid AS srcnodeid,
				c.label, c.type AS cabletype, c.capacity, c.distance, c.colorschemaid, 
				c.dstnodeid, n2.name AS dstnetnode,
				(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netelements e
			LEFT JOIN netnodes n ON (n.id = e.netnodeid)
			LEFT JOIN netcables c ON (c.netelemid = e.id) 
			LEFT JOIN netnodes n2 ON (n2.id = c.dstnodeid)
			LEFT JOIN location_cities lc ON (lc.id = n.location_city)
			LEFT JOIN location_streets lst ON (lst.id = n.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE e.id = ?', array($id));
        #$result['takenports'] = $this->CountNetElemLinks($id);
        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;
        $result['projectname'] = trans('none');
        if ($result['invprojectid']) {
            $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($result['invprojectid']));
            if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                    /* inherited */
                    if ($netnode) {
                        $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",array($netnode['invprojectid']));
                            if ($prj)
                                $result['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                    }
                } else
                    $result['projectname'] = $prj['name'];
            }
        }
        return $result;

    }

    public function GetNetElemSplitter($id)
    {
        $result = $this->db->GetRow('SELECT e.*,
				n.name AS nodename,
				(SELECT COUNT(*) FROM netports WHERE netelemid=e.id AND type=201) AS inports,
				(SELECT COUNT(*) FROM netports WHERE netelemid=e.id AND type=202) AS outports,
				(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netelements e
			LEFT JOIN netnodes n ON (n.id = e.netnodeid)
			LEFT JOIN location_cities lc ON (lc.id = n.location_city)
			LEFT JOIN location_streets lst ON (lst.id = n.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE e.id = ?', array($id));
        $result['takenports'] = $this->CountNetElemLinks($id);

	#$result['location'] = $result['city_name'].', '.$result['street_type'].' '.$result['street_name'];
	
        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;
        $result['projectname'] = trans('none');
        if ($result['invprojectid']) {
            $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($result['invprojectid']));
            if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                    /* inherited */
                    if ($netnode) {
                        $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",array($netnode['invprojectid']));
                            if ($prj)
                                $result['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                    }
                } else
                    $result['projectname'] = $prj['name'];
            }
        }
        return $result;

    }

    public function GetNetElemMultiplexer($id)
    {
        $result = $this->db->GetRow('SELECT e.*,
				n.name AS nodename,
				(SELECT COUNT(*) FROM netports WHERE netelemid=e.id AND type=201) AS inports,
				(SELECT COUNT(*) FROM netports WHERE netelemid=e.id AND type=202) AS outports,
				(CASE WHEN lst.name2 IS NOT NULL THEN ' . $this->db->Concat('lst.name2', "' '", 'lst.name') . ' ELSE lst.name END) AS street_name,
				lt.name AS street_type,
				lc.name AS city_name,
				lb.name AS borough_name, lb.type AS borough_type,
				ld.name AS district_name, ls.name AS state_name
			FROM netelements e
			LEFT JOIN netnodes n ON (n.id = e.netnodeid)
			LEFT JOIN location_cities lc ON (lc.id = n.location_city)
			LEFT JOIN location_streets lst ON (lst.id = n.location_street)
			LEFT JOIN location_street_types lt ON (lt.id = lst.typeid)
			LEFT JOIN location_boroughs lb ON (lb.id = lc.boroughid)
			LEFT JOIN location_districts ld ON (ld.id = lb.districtid)
			LEFT JOIN location_states ls ON (ls.id = ld.stateid)
			WHERE e.id = ?', array($id));
        $result['takenports'] = $this->CountNetElemLinks($id);

	#$result['location'] = $result['city_name'].', '.$result['street_type'].' '.$result['street_name'];
	
        if ($result['guaranteeperiod'] != NULL && $result['guaranteeperiod'] != 0)
            $result['guaranteetime'] = strtotime('+' . $result['guaranteeperiod'] . ' month', $result['purchasetime']); 
        elseif ($result['guaranteeperiod'] == NULL)
            $result['guaranteeperiod'] = -1;
        $result['projectname'] = trans('none');
        if ($result['invprojectid']) {
            $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id = ?", array($result['invprojectid']));
            if ($prj) {
                if ($prj['type'] == INV_PROJECT_SYSTEM && intval($prj['id'])==1) {
                    /* inherited */
                    if ($netnode) {
                        $prj = $this->db->GetRow("SELECT * FROM invprojects WHERE id=?",array($netnode['invprojectid']));
                            if ($prj)
                                $result['projectname'] = trans('$a (from network node $b)', $prj['name'], $netnode['name']);
                    }
                } else
                    $result['projectname'] = $prj['name'];
            }
        }
        return $result;
    }

    public function GetNetElemComputer($id)
    {

    }
    public function NetElemDelLinks($id)
    {
        global $SYSLOG_RESOURCE_KEYS;

        if ($this->syslog) {
            $netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE src=? OR dst=?', array($id, $id));
            if (!empty($netlinks))
                foreach ($netlinks as $netlink) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['src'],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['dst'],
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
            $nodes = $this->db->GetAll('SELECT id, netdev, ownerid FROM vnodes WHERE netdev=? AND ownerid>0', array($id));
            if (!empty($nodes))
                foreach ($nodes as $node) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node['id'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST] => $node['ownerid'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => 0,
                        'port' => 0,
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM]));
                }
        }
        $this->db->Execute('DELETE FROM netlinks WHERE src=? OR dst=?', array($id, $id));
        $this->db->Execute('UPDATE nodes SET netdev=0, port=0 
				WHERE netdev=? AND ownerid>0', array($id));
    }

    public function DeleteNetElem($id)
    {
        global $SYSLOG_RESOURCE_KEYS;

        $this->db->BeginTrans();
        if ($this->syslog) {
            $netlinks = $this->db->GetAll('SELECT id, src, dst FROM netlinks WHERE src = ? OR dst = ?', array($id, $id));
            if (!empty($netlinks))
                foreach ($netlinks as $netlink) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETLINK] => $netlink['id'],
                        'src_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['src'],
                        'dst_' . $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $netlink['dst'],
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NETLINK, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
            $nodes = $this->db->GetCol('SELECT id FROM vnodes WHERE ownerid = 0 AND netdev = ?', array($id));
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
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $id,
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_DELETE, $args, array_keys($args));
                }
            $nodes = $this->db->GetAll('SELECT id, ownerid FROM vnodes WHERE ownerid <> 0 AND netdev = ?', array($id));
            if (!empty($nodes))
                foreach ($nodes as $node) {
                    $args = array(
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NODE] => $node['id'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_CUST] => $node['ownerid'],
                        $SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => 0,
                    );
                    $this->syslog->AddMessage(SYSLOG_RES_NODE, SYSLOG_OPER_UPDATE, $args, array_keys($args));
                }
            $args = array($SYSLOG_RESOURCE_KEYS[SYSLOG_RES_NETELEM] => $id);
            $this->syslog->AddMessage(SYSLOG_RES_NETELEM, SYSLOG_OPER_DELETE, $args, array_keys($args));
        }
        $this->db->Execute('DELETE FROM netlinks WHERE src=? OR dst=?', array($id, $id));
        $this->db->Execute('DELETE FROM nodes WHERE ownerid=0 AND netdev=?', array($id));
        $this->db->Execute('UPDATE nodes SET netdev=0 WHERE netdev=?', array($id));
        $this->db->Execute('DELETE FROM netdevices WHERE id=?', array($id));
        $this->db->CommitTrans();
    }

    public function GetModelList($pid = NULL) {
        if (!$pid)
                return NULL;
        $list = $this->db->GetAll('SELECT m.id, m.type, m.name, m.alternative_name,
                        (SELECT COUNT(i.id) FROM netelements i WHERE i.model = m.id) AS netdevcount
                        FROM netdevicemodels m
                        WHERE m.netdeviceproducerid = ?
                        ORDER BY m.name ASC',
                        array($pid));
	return $list;
    }

    public function GetNetElemPorts($id) {
	$ports = $this->db->GetAll('SELECT * FROM netports WHERE netelemid=? ORDER BY label ASC',array($id));
	if (is_array($ports)) foreach ($ports AS $idx => $port) {
	    $rs = $this->db->GetRow('SELECT * FROM netradiosectors WHERE netportid=?',array($port['id']));
	    if (is_array($rs))
		    $ports[$idx]['radiosector']=$rs;
	    $conns=$this->db->GetAll('SELECT * FROM netconnections WHERE ports ?LIKE? ?',array("%".$port['id']."%"));
	    $ports[$idx]['taken']=count($conns);
	    if (is_array($conns)) foreach ($conns AS $conn) {
		if (preg_match('/^([0-9]*):([0-9]*)$/',$conn['wires'],$wires) and $port['type']==300) {
		# spaw
			$ports[$idx]['wire1']=$this->db->GetRow("SELECT netelements.name,netcables.label,netwires.* FROM netwires LEFT JOIN netelements ON netelements.id=netwires.netcableid LEFT JOIN netcables ON netcables.id=netwires.netcableid WHERE netwires.id=?",array($wires[1]));
			$ports[$idx]['wire2']=$this->db->GetRow("SELECT netelements.name,netcables.label,netwires.* FROM netwires LEFT JOIN netelements ON netelements.id=netwires.netcableid LEFT JOIN netcables ON netcables.id=netwires.netcableid WHERE netwires.id=?",array($wires[2]));
			$ports[$idx]['connections'][]='Spaw: '.$this->GetCableDescByWire($wires[1]).'|'.$this->GetCableDescByWire($wires[2]);
		} elseif (preg_match('/^([0-9]*):([0-9]*)$/',$conn['ports'],$cports)) {
		# patchcord
			if ($cports[1]==$id) $cport=$cports[2];
			else $cport=$cports[1];
			$ports[$idx]['port']=$this->db->GetRow("SELECT netelements.name,netports.* FROM netports LEFT JOIN netelements ON netelements.id=netports.netelemid WHERE netports.id=?",array($cport));
		} else {
		# pigtail
			$ports[$idx]['wire']=$this->db->GetRow("SELECT netelements.name,netcables.label,netwires.* FROM netwires LEFT JOIN netelements ON netelements.id=netwires.netcableid LEFT JOIN netcables ON netcables.id=netwires.netcableid WHERE netwires.id=?",array($conn['wires']));
		}
	    }
	}
	#echo '<PRE>';print_r($ports);echo '</PRE>';
	return($ports);
    }

    public function GetNetElemUnconnectedList($netnodeid,$id) {
	$type=$this->GetNetElemType($id);
	switch ($type) { // lista typów ktore mozemy laczyc ze soba
	case 0: // aktywne z: aktywnym, pasywnym, splitterem, mux, computer
		$typelist="0,1,3,4,5";
		break;
	case 1: // pasywne - z wszystkim
		$typelist="0,1,2,3,4,5";
		break;
	case 2: // kabel
		$typelist="0,1,4,5";
		break;
	case 3: // splitter
		$typelist="0,1,2,3,4,5";
		break;
	case 4: // multiplexer
		$typelist="0,1,2,3,4,5";
		break;
	case 99: // komputer
		$typelist="0,1,2,3,4,5";
		break;
	default:
		return FALSE;
	}
	$elems=$this->db->GetAll("SELECT * FROM netelements WHERE type IN (?) AND netnodeid=? AND id<>?",array($typelist,$netnodeid,$id));
	foreach ($elems AS $id => $elem) {
		
	}
	echo '<PRE>';print_r($elems);echo '</PRE>';
	return($elems);
    }

    public function GetConnectionForWire($id,$srcnodeid,$dstnodeid) {
        $netwires=$this->db->GetAll("SELECT * FROM netconnections WHERE wires ?LIKE? ?",array("%".$id."%"));
	#echo '<HR>netwires:<PRE>';print_r($netwires);echo '</PRE>';
	if (count($netwires)==0) 
		return(array());
	foreach ($netwires AS $wires) {
		$port=$this->db->GetRow("SELECT * FROM netports WHERE id=?",array($wires['ports']));
		$pelem=$this->db->GetRow("SELECT * FROM netelements WHERE id=?",array($port['netelemid']));
		$pelem['port']=$port;
		#echo 'port:<PRE>';print_r($port);echo '</PRE>';
		if ($port['type']<>300) {
			// port nie jest tacka - kabel w port
			$elem=$pelem;
			$elem['connid']=$wires['id'];
		} elseif (preg_match('/^([0-9]+):([0-9]+)$/',$wires['wires'],$x)) {
			// kabel+kabel w tacke
			if ($x[1]==$id) 
			    $wire=$this->db->GetRow("SELECT * FROM netwires WHERE id=?",array($x[2]));
			else 
			    $wire=$this->db->GetRow("SELECT * FROM netwires WHERE id=?",array($x[1]));
			$elem=$this->db->GetRow("SELECT * FROM netelements WHERE id=?",array($wire['netcableid']));
			$elem['wire']=$wire;
			$elem['cable']=$this->db->GetRow("SELECT * FROM netcables WHERE netelemid=?",array($wire['netcableid']));
			#if ($elem['netnodeid']==$
			$elem['tray']=$pelem;
			$elem['connid']=$wires['id'];
		}
		#echo "($srcnodeid,$dstnodeid) -> ".$pelem['netnodeid']."<BR>";
		if ($pelem['netnodeid']==$srcnodeid) 
			$conn['src']=$elem;
		else
			$conn['dst']=$elem;
		unset($elem);
	}
	#echo 'conn:<PRE>';print_r($conn);echo '</PRE>';
	return($conn);
    }

    public function GetConnPossForPort($portid) {
	global $NETTECHNOLOGIES;
	$port=$this->db->GetRow("SELECT * FROM netports WHERE id=?",array($portid));
	#echo '<PRE>';print_r($port);echo '</PRE>';
	$taken=$this->db->GetAll('SELECT * FROM netconnections WHERE ports ?LIKE? ?',array("%".$port['id']."%"));
	if (count($taken)==$port['capacity'] OR $port['type']==300) {
		return(array());
	}
	$element=$this->db->GetRow("SELECT * FROM netelements WHERE id=?",array($port['netelemid']));
	$list=$this->db->GetAll("SELECT netelements.id,netelements.type,netelements.name FROM netelements LEFT JOIN netcables ON netcables.netelemid=netelements.id WHERE (netelements.netnodeid=? OR netcables.dstnodeid=?) and netelements.id<>?",array($element['netnodeid'],$element['netnodeid'],$element['id']));
	$connector=$port['connectortype'];
        $porttype=$port['type'];
	if ($port['type']==0 OR $element['type']==99) {
		// aktywne
		$portmedium=$NETTECHNOLOGIES[$port['technology']]['medium'];
	} else {
		// pasywne
		if ($port['technology']>0)
			$portmedium=$NETTECHNOLOGIES[$port['technology']]['medium'];
		else
			$portmedium=$port['type'];
	}
        $portmedium=floor($porttype/100);
	foreach ($list AS $id => $elem) {
		if ($elem['type']==0) {
			#echo "ACTIVE [".$elem['type']."]: ".$elem['name'].'<BR>';
			$ports=$this->GetNetElemPorts($elem['id']);
			#echo '<UL>';
			foreach ($ports AS $pid => $port) {
				#echo "<LI>PORT [".$port['label']."]: ".$port['type']." '".$NETTECHNOLOGIES[$technology['medium']]."' ";
				if ($port['capacity']==$port['taken']) {
					#echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
					unset($ports[$pid]);
				} else {
                                        $portmedium=floor($port['type']/100);
                                        $mediumfit=($portmedium-$wiremedium) ? 0 : 1;
                                        #echo ' pasuje='.$mediumfit.' ';
                                        if (!$mediumfit)
                                                unset($ports[$pid]);
                                }
                                #echo '</LI>';
                        }
                        if (count($ports)) {
                                #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                $list[$id]['ports']=$ports;
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }
                        #echo '</UL>';
                } elseif ($elem['type']==1)  {
                        #echo "PASSIVE [".$elem['id']."]: ".$elem['name'].'<BR>';
                        $ports=$this->GetNetElemPorts($elem['id']);
                        #echo '<UL>';
                        foreach ($ports AS $pid => $port) {
                                #echo "<LI>PORT [".$port['label']."] ".$port['type'].": ";
                                if ($port['capacity']==$port['taken']) {
                                        #echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
                                        unset($ports[$pid]);
                                } elseif ($port['type']==300) {
                                        #echo ' tacka na spawy ';
                                        unset($ports[$pid]);
                                } else {
					// DOPASOWANIE PORT - PORT (w zasadzie dowolnosc!)
					#echo $port['type'].'=='.$porttype.' - ';
					if (($port['type']==1 and $porttype==1) or
					    ($port['type']==2 and $porttype==2)) { // MIEDZ - DOPASOWANIE
						$fit=$connector==$port['connectortype'];
						#echo 'miedź '.$connector.'=='.$port['connectortype'];
					} elseif (($porttype>=200 and $porttype<300) and
				                  ($port['type']>=200 and $porttype<300)) {
						$fit=1;
						#echo 'swiatło';
					} else {
						$fit=0;
					}
                                        #echo ' pasuje='.$fit;
                                        if (!$fit)
                                                unset($ports[$pid]);
                                }
                                #echo "</LI>";
                        }
                        if (count($ports)) {
                                if (count($ports)) {
                                        #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                        $list[$id]['ports']=$ports;
                                }
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }
                        #echo '</UL>';
                } elseif ($elem['type']==2)  {
                        #echo "CABLE [".$elem['id']."]: ".$elem['name'].'<BR>';
                        $wires=$this->db->GetAll("select * from netwires where netcableid=? and id not in (select n1.id from netwires n1 left join netconnections nc on nc.wires regexp(n1.id) left join netports np on nc.ports=np.id left join netelements ne on ne.id=np.netelemid where n1.netcableid=? and ne.netnodeid<>?)",array($elem['id'],$elem['id'],$element['netnodeid']));
			#echo '<PRE>';print_r($this->db->GetErrors());echo '</PRE>';
                        #echo '<UL>';
                        if (is_array($wires)) foreach ($wires AS $wid => $wire) {
                                #echo '<LI>#'.$wire['id'].' tuba #'.$wire['bundle'].' włókno #'.$wire['wire'].' ';
                                #echo ' polaczenia: '.$cc.' type: '.$wire['type'];
                                if ( ($wire['type']<100 AND $wire['type']<>$wiretype) OR // miedz musi pasowac do siebie
                                     ($wiretype<250 AND $wire['type']>=250) OR // SM do MM
                                     ($wiretype>=250 AND $wire['type']<250)  // MM do SM
                                ) {
                                        unset($wires[$wid]);
                                        #echo '- usuwamy wlokno <BR>';
                                } # else echo 'wlokno #'.$wire['id'].'|'.$wire['bundle'].'|'.$wire['wire'].' ('.$wire['type'].') jest OK<BR>';
                        }
                        if (count($wires)) { // Nie ma pasujących włókien!
                                #echo '<LI>Pasujące włókna: <PRE>';print_r($wires);echo '</PRE></LI>';
                                $list[$id]['wires']=$wires;
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących włókien</LI>';
                        }
                        #echo '</UL>';
                } elseif ($elem['type']==3 OR $elem['type']==4)  {
                        #echo "SPLITTER/MUX[".$elem['type']."]: ".$elem['name'].'<BR>';
                        $ports=$this->GetNetElemPorts($elem['id']);
                        #echo '<UL>';
                        foreach ($ports AS $pid => $port) {
                                #echo '<LI>'.$port['label'].' '.$port['type'].' ';
                                if ($port['connectortype']==999) {
                                        #echo ' spaw!';
                                        unset($ports[$pid]);
                                } elseif ($port['capacity']==$port['taken']) {
                                        #echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
                                        unset($ports[$pid]);
                                } else {
					$fit=1;
                                        #echo ' pasuje='.$fit.' ';
                                        if (!$fit)
                                                unset($ports[$pid]);
                                }
                                #echo '</LI>';
                        }
                        if (count($ports)) {
                                #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                $list[$id]['ports']=$ports;
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }
                        #echo '</UL>';
                } elseif ($elem['type']==99)  {
                        #echo "COMPUTER [".$elem['type']."]: ".$elem['name'].'<BR>';

                }
	}
        #echo '<HR>Błędy bazy:<PRE>';print_r($this->db->GetErrors());echo '</PRE>';
	if (is_array($connlist))
	       	ksort($connlist);
        return($connlist);
    }

    public function GetConnPossForWire($nodeid,$wireid) {
	global $NETTECHNOLOGIES;
	$wire=$this->db->GetRow("SELECT * FROM netwires WHERE id=?",array($wireid));
	$cableid=$wire['netcableid'];
        $cable=$this->db->GetRow("SELECT * FROM netelements WHERE id=?",array($cableid));
	$cabletype=$this->db->GetOne("SELECT type FROM netcables WHERE netelemid=?",array($cableid));
	$wiretype=$wire['type'];
        $wiremedium=floor($wiretype/100);
	#echo "Kabel: id=$cableid,cabletype=$cabletype,wiretype=$wiretype<BR>";
        $list=$this->db->GetAll("SELECT id,type,name FROM netelements WHERE (netnodeid=? OR id IN (SELECT netelemid FROM netcables WHERE dstnodeid=?)) AND id<>? ORDER BY name ASC",array($_GET['netnodeid'],$_GET['netnodeid'],$cableid));
        if (count($list))
        foreach ($list AS $id => $elem) {
                if ($elem['type']==0) {
                	#echo "ACTIVE [".$elem['type']."]: ".$elem['name'].'<BR>';
                        $ports=$this->GetNetElemPorts($elem['id']);
                        #echo '<UL>';
                        foreach ($ports AS $pid => $port) {
                                #echo "<LI>PORT [".$port['label']."]: ".$port['type']." '".$NETTECHNOLOGIES[$technology['medium']]."' ";
				if ($port['capacity']==$port['taken']) {
					#echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
					unset($ports[$pid]);
				} else {
					$portmedium=floor($port['type']/100);
					$mediumfit=($portmedium-$wiremedium) ? 0 : 1;
					#echo ' pasuje='.$mediumfit.' ';
					if (!$mediumfit)
						unset($ports[$pid]);
				}
				#echo '</LI>';
                        }
                        if (count($ports)) {
                                #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                $list[$id]['ports']=$ports;
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }			
                        #echo '</UL>';
                } elseif ($elem['type']==1)  {
                	#echo "PASSIVE [".$elem['type']."]: ".$elem['name'].'<BR>';
                        $wiremedium=floor($wiretype/100);
                        $ports=$this->GetNetElemPorts($elem['id']);
                        #echo '<UL>';
                        foreach ($ports AS $pid => $port) {
                                #echo "<LI>PORT [".$port['label']."] ".$port['type'].": ";
                                if ($port['capacity']==$port['taken']) {
                                        #echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
                                        unset($ports[$pid]);
				} elseif ($port['type']==300) {
					#echo ' tacka na spawy ';
					$trays[$pid]=$port;
					unset($ports[$pid]);
                                } else {
                                        $portmedium=floor($port['type']/100);
                                        $mediumfit=($portmedium-$wiremedium) ? 0 : 1;
                                        #echo ' pasuje='.$mediumfit.' ';
                                        if (!$mediumfit)
                                                unset($ports[$pid]);
                                }
                                #echo "</LI>";
                        }
                        if (count($ports) OR count($trays)) {
                                if (count($ports)) {
                                        #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                        $list[$id]['ports']=$ports;
                                }
                                if (count($trays)) {
                                        #echo '<LI>Pasujące tacki: <PRE>';print_r($trays);echo '</PRE></LI>';
                                        $list[$id]['trays']=$trays;
                                }
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }
                        #echo '</UL>';
                } elseif ($elem['type']==2)  {
                	#echo "CABLE [".$elem['type']."]: ".$elem['name'].'<BR>';
                        $wires=$this->db->GetAll("SELECT * FROM netwires WHERE netcableid=?",array($elem['id']));
			#echo '<UL>';
                        if (is_array($wires)) foreach ($wires AS $wid => $wire) {
				#echo '<LI>#'.$wire['id'].' tuba #'.$wire['bundle'].' włókno #'.$wire['wire'].' ';
                                $cc=$this->db->GetOne("SELECT count(*) FROM netconnections WHERE wires ?LIKE? ?",array('%'.$wire['id'].'%'));
				#echo ' polaczenia: '.$cc.' type: '.$wire['type'];	
                                if (    ($cc) OR  // włólno zajęte
                                        ($wire['type']<100 AND $wire['type']<>$wiretype) OR // miedz musi pasowac do siebie
                                        ($wiretype<250 AND $wire['type']>=250) OR // SM do MM
                                        ($wiretype>=250 AND $wire['type']<250)  // MM do SM
                                ) {
                                        unset($wires[$wid]);
                                        #echo '- usuwamy wlokno <BR>';
                                }  #else echo 'wlokno #'.$wire['id'].'|'.$wire['bundle'].'|'.$wire['wire'].' ('.$wire['type'].') jest OK<BR>';
                        }
                        if (count($wires)) { // Nie ma pasujących włókien!
				#echo '<LI>Pasujące włókna: <PRE>';print_r($wires);echo '</PRE></LI>';
                                $list[$id]['wires']=$wires;
				$connlist[$elem['type']][$elem['id']]=$list[$id];
			} else {
				#echo '<LI>Brak pasujących włókien</LI>';
			}
			#echo '</UL>';
                } elseif ($elem['type']==3 OR $elem['type']==4)  {
                	#echo "SPLITTER/MUX [".$elem['type']."]: ".$elem['name'].'<BR>';
			$ports=$this->GetNetElemPorts($elem['id']);
			#echo '<UL>';
			foreach ($ports AS $pid => $port) {
				#echo '<LI>'.$port['label'].' '.$port['type'].' ';
				if ($port['connectortype']) {
					#echo ' z konektorem!';
					unset($ports[$pid]);
				} elseif ($port['capacity']==$port['taken']) {
                                        #echo ' zajęty '.$port['taken'].'/'.$port['capacity'];
                                        unset($ports[$pid]);
				} else {
                                        $portmedium=floor($port['type']/100);
                                        $mediumfit=($portmedium-$wiremedium) ? 0 : 1;
                                        #echo ' pasuje='.$mediumfit.' ';
                                        if (!$mediumfit)
                                                unset($ports[$pid]);
				}
				#echo '</LI>';
			}
                        if (count($ports)) {
                                #echo '<LI>Pasujące porty: <PRE>';print_r($ports);echo '</PRE></LI>';
                                $list[$id]['ports']=$ports;
                                $connlist[$elem['type']][$elem['id']]=$list[$id];
                        } else {
                                #echo '<LI>Brak pasujących portów</LI>';
                        }
			#echo '</UL>';
                } elseif ($elem['type']==99)  {
                	echo "COMPUTER [".$elem['type']."]: ".$elem['name'].'<BR>';

                }
        }

        #echo '<HR>Błędy bazy:<PRE>';print_r($this->db->GetErrors());echo '</PRE>';
	if (is_array($connlist))
		ksort($connlist);
	return($connlist);
    }

 	public function GetCableDescByWire($wireid) {
		$cable=$this->db->GetRow("SELECT bundle,wire,netelements.name FROM netwires LEFT JOIN netelements ON netelements.id=netwires.netcableid WHERE netwires.id=?",array($wireid));
		return($cable['name'].'/#'.$cable['bundle'].'/#'.$cable['wire']);

	}

	public function GetCableByWire($wireid) {
		$wire=$this->db->GetRow("SELECT * FROM netwires WHERE id=?",array($wireid));
		$cable=$this->GetNetElemCable($wire['netcableid']);
		$cable['wire']=$wire;
		return($cable);
	}


	public function GetElementByPort($portid) {
		$port=$this->db->GetRow("SELECT * FROM netports WHERE id=?",array($portid));
		$port['taken']=$this->db->GetOne("SELECT count(*) FROM netconnections WHERE ports ?LIKE? ?",array("%".$portid."%"));
		$netelem=$this->db->GetRow("SELECT * FROM netelements WHERE id=?",array($port['netelemid']));
		$rs = $this->db->GetRow('SELECT * FROM netradiosectors WHERE netportid=?',array($portid));
		if (is_array($rs)) $port['radiosector']=$rs;
		$netelem['port']=$port;
		return($netelem);
	}
}
