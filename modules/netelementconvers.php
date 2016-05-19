<?


// *******************************************************************
// Przeniesienie informacji u urządzeniach
// Etap 1. - przeniesienie danych z netdevices_old do netelements
// *******************************************************************

$netlinkid=$DB->GetOne("SELECT MAX(id) FROM netlinks_old");
#echo "max: $netlinkid<BR>";
echo '<H1>Import urządzeń aktywnych i połączeń logicznych</H1>';
#$devices=$DB->GetAll("SELECT * FROM netdevices_old WHERE name='tarpno4-ap' ORDER BY id ASC");
$devices=$DB->GetAll("SELECT * FROM netdevices_old  ORDER BY id ASC");
if (is_array($devices)) foreach ($devices AS $dev) {
	echo '<B>'.$dev['name'].'</B>: <I>'.$dev['producer'].' '.$dev['model'].'</I><UL>';
	$check=$DB->GetOne("SELECT id FROM netelements WHERE id=".$dev['id']);
	if (!isset($check)) {
		if (!isset($dev['netnodeid'])) {
			$location=array('devid'=>$dev['id'],'devname'=>$dev['name'],'status'=>$dev['status'],'location'=>$dev['location'],'location_city'=>$dev['location_city'],'location_street'=>$dev['location_street'],'location_house'=>$dev['location_house'],'location_flat'=>$dev['location_flat'],'latitude'=>$dev['latitude'],'longitude'=>$dev['longitude'],'invprojectid'=>$dev['invprojectid'],'divisionid'=>$dev['divisionid']);
			$location=check_location($location);
			if (!isset($location['netnodeid'])) {
				$dev['netnodeid']=netnodeadd($location);
			} else {
				$dev['netnodeid']=$location['netnodeid'];
			}
		} else {
			echo '<LI><FONT color="green">Urządzenie już przypisane do węzła</FONT></LI>';
		}
		$DB->Execute("INSERT INTO netelements VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",array($dev['id'],$dev['name'],0,$dev['description'],$dev['producer'],$dev['model'],$dev['serialnumber'],$dev['purchasetime'],$dev['guaranteeperiod'],$dev['netnodeid'],$dev['invprojectid'],$dev['netdevicemodelid'],$dev['status']));
		$DB->Execute("INSERT INTO netdevices VALUES (?,?,?,?,?,?,?,?,?)",array(null,$dev['id'],$dev['shortname'],$dev['nastype'],$dev['clients'],'',$dev['secret'],$dev['community'],$dev['channelid']));
		echo '<LI><font color="green">Ilość portów: '.$dev['ports'].'</font><UL>';
		$tech=array();
		$nls=$DB->GetAll("SELECT * FROM netlinks_old WHERE src=".$dev['id']." OR dst=".$dev['id']);
		$copper=array();
		if (is_array($nls)) foreach ($nls AS $ls) {
			if ($ls['technology']>=100 and $ls['technology']<200) {
				if ($ls['src']==$dev['id']) {
					$tech[$ls['technology']][$ls['dstradiosector']][$ls['id']]=1;
				} else {
					$tech[$ls['technology']][$ls['srcradiosector']][$ls['id']]=1;
				}
			} else {
				if ($ls['src']==$dev['id']) {
					$tech[$ls['technology']][$ls['dstport']][$ls['id']]=1;
					if ($ls['dstport']) {
						if ($ls['technology']<100) {
							$copper[$ls['dstport']]=1;
						} else {
							$fiber[$ls['dstport']]=1;
						}
					}
				} else {
					$tech[$ls['technology']][$ls['srcport']][$ls['id']]=1;
					if ($ls['srcport']) {
						if ($ls['technology']<100) {
							$copper[$ls['srcport']]=1;
						} else {
							$fiber[$ls['srcport']]=1;
						}
					}
				}
						
						
			}
		}
		$nps=$DB->GetAll("SELECT * FROM nodes WHERE ownerid>0 AND netdev=".$dev['id']);
		if (is_array($nps)) foreach ($nps AS $np) {
			if ($np['linktechnology']>=100 and $np['linktechnology']<200) {
				#if ($np['linkradiosector'])
					$tech[$np['linktechnology']][$np['linkradiosector']]['n'.$np['id']]=1;
				#else
				#	$tech[$np['linktechnology']][0]['node'][$np['id']]=1;
			} else {
				$tech[$np['linktechnology']][$np['port']]['n'.$np['id']]=1;
				if ($np['port']) {
					if ($np['linktechnology']<100) {
						$copper[$np['port']]=1;
					} else {
						$fiber[$np['port']]=1;
					}
				}

			}
		}
		#echo '<HR>tech:<PRE>';print_r($tech);echo '</PRE>';
		#cho '<PRE>copper:';print_r($copper);echo '</PRE>';
		$ports=array();
		$wport_num=1; $cport_num=0; $fport_num=0;
		if (is_array($tech)) foreach ($tech AS $t => $p ) {
			if ($t<100) {
				foreach ($p AS $nr => $i) {
					if (preg_match('/^([0-9]+),?/',$NETTECHNOLOGIES[$t]['connector'],$c))
						$connector=$c[1];
					foreach ($i AS $link => $x) {
						if ($nr) {
							$num=$nr;
						} else {
							for ($cport_num++;isset($copper[$cport_num]);$cport_num++);
							$num=$cport_num;
						}
						$port=array(
							'id' 		=> NULL,
							'netelemid'	=> $dev['id'],
							'type'		=> 1,
							'label'		=> 'copper'.$num,
							'connectortype'	=> $connector,
							'technology'	=> $t,
							'capacity'	=> 1
						);
						$ports[$link]=$port;
					}
				}
			} elseif ($t<200) {
				foreach ($p AS $rs => $i) {
					if ($rs>0) {
						$connected=array();
						foreach ($i AS $link => $x) {
							$connected[$link]=1;
						}
						$rss=$DB->GetAll("SELECT * FROM netradiosectors_old WHERE id=".$rs);
						$rss=$rss[0];
						$rs=array(
							'id'		=> $rss['id'],
							'netportid'	=> NULL,
							'name'		=> $rss['name'],
							'azimuth'	=> $rss['azimuth'],
							'width'		=> $rss['width'],
							'altitude'	=> $rss['altitude'],
							'rsrange'	=> $rss['rsrange'],
							'license'	=> $rss['license'],
							'frequency'	=> $rss['frequency'],
							'frequency2'	=> $rss['frequency2'],
							'bandwidth'	=> $rss['bandwidth']
						);
						$port=array(
                                                        'id'            => NULL,
                                                        'netelemid'     => $dev['id'],
                                                        'type'          => 100,
                                                        'label'         => 'wireless'.$wport_num++,
                                                        'connectortype' => $NETTECHNOLOGIES[$t]['connector'],
                                                        'technology'    => $t,
                                                        'capacity'      => 100,
							'radiosector'	=> $rs,
							'connected'	=> $connected
                                                );
						$ports[$rss['id']]=$port;

					} else { 
						foreach ($i AS $link => $x) {
							$port=array(
								'id'            => NULL,
								'netelemid'     => $dev['id'],
								'type'          => 100,
								'label'         => 'wireless'.$wport_num++,
								'connectortype' => $NETTECHNOLOGIES[$t]['connector'],
								'technology'    => $t,
								'capacity'      => 1
							);
							$ports[$link]=$port;
						}
					}
				}

			} elseif ($t<300) {
                                foreach ($p AS $nr => $i) {
                                        if (preg_match('/^([0-9]+),?/',$NETTECHNOLOGIES[$t]['connector'],$c))
                                                $connector=$c[1];
					foreach ($i AS $link => $x) {
                                                if ($nr) {
                                                        $fport_num=$fport;
                                                } else {
                                                        for ($port_num++;isset($fiber[$fport_num]);$fport_num++);
							$fport=$fport_num;
                                                }
                                                $port=array(
                                                        'id'            => NULL,
                                                        'netelemid'     => $dev['id'],
                                                        'type'          => 200,
                                                        'label'         => 'fiber'.$fport,
                                                        'connectortype' => $connector,
                                                        'technology'    => $t,
                                                        'capacity'      => 1
                                                );
                                                $ports[$link]=$port;
                                        }
                                }	
			} else {
				echo '<LI><FONT color="red">Nieznana technologia '.$t.'</FONT></LI>';
			}
		}
		#echo '<HR>Ports:<PRE>';print_r($ports);echo '</PRE>';
		foreach ($ports AS $link => $port) {
			if (isset($port['radiosector'])) {
				$radiosector=$port['radiosector'];
				unset($port['radiosector']);
				$connected=$port['connected'];
				unset($port['connected']);
			} else {
				$connected=array();
				$connected[$link]=1;
				unset($radiosector);
			}
			echo '<LI><FONT COLOR="blue">'.$port['label'].'</FONT><UL>';
			$DB->Execute("INSERT INTO netports VALUES (?,?,?,?,?,?,?)",array_values($port));
			$netportid=$DB->GetLastInsertID('netports');
			if (isset($radiosector)) {
				$radiosector['netportid']=$netportid;
				$DB->Execute("INSERT INTO netradiosectors VALUES (?,?,?,?,?,?,?,?,?,?,?)",array_values($radiosector));
				#echo '<PRE>';print_r($DB->GetErrors());echo '</PRE>';
				echo '<LI><FONT COLOR="violet">['.$radiosector['name'].'] </FONT></LI>';
			}
			if (is_array($connected)) {
				echo '<LI><FONT COLOR="purple">Podłączenia:</LI><UL>';
				foreach ($connected AS $linkid => $x) {
					echo '<LI>';
					if (preg_match('/^n([0-9]+)$/',$linkid,$nodeid)) {
						$nodeid=$nodeid[1];
						$nodes=$DB->GetALL("SELECT * FROM nodes WHERE id=?",array($nodeid));
						$node=$nodes[0];
						#$DB->Execute("UPDATE nodes SET netport=? WHERE id=?",array($netportid,$nodeid));
						$DB->Execute("INSERT INTO netlinks VALUES (?,?,?,?,?,?,?)",array($netlinkid++,$netportid,NULL,$node['id'],$node['linktype'],$node['linkspeed'],$node['linktechnology']));
						#echo '<PRE>';print_r($DB->GetErrors());echo '</PRE>';
						echo ' podłączono node #'.$nodeid;
					} else {
						$l=$DB->GetAll("SELECT * FROM netlinks WHERE id=?",array($linkid));
						$lo=$DB->GetAll("SELECT * FROM netlinks_old WHERE id=?",array($linkid));
						$lo=$lo[0];
						if (!count($l)) {	
							$DB->Execute("INSERT INTO netlinks VALUES (?,?,?,?,?,?,?)",array($linkid,$netportid,NULL,NULL,$lo['type'],$lo['speed'],$lo['technology']));
							echo ' podłączono jako src';
						} else {
							$l=$l[0];
							$DB->Execute("UPDATE netlinks SET dstport=? WHERE id=?",array($netportid,$linkid));
							echo ' podłączono jako dst do netport #'.$l['srcport'];
						}
					}	
					
					echo '</LI>';	
				}
				echo '</UL></FONT></LI>';
			}
			echo '</UL></LI>';
		}
		echo '</UL>';
	} else {
		echo '<LI><FONT COLOR="red"> juz zaimportowane!</FONT></LI>';
	}
	echo '</UL>';
}

echo '<H1>Import połączeń logicznych do połączeń fizycznych</H1>';
$netlinks = $DB->GetAll("SELECT * FROM netlinks ORDER BY id ASC");
if (is_array($netlinks)) foreach ($netlinks AS $link) {
	if (isset($link['dstport']))
		$dst=$link['dstport'];
	else
		$dst='n'.$link['node'];
	$DB->Execute("INSERT INTO netconnections VALUES (?,?,?,?,?)",array(NULL,'',$link['srcport'].":".$dst,'',''));
	$connid=$DB->GetLastInsertID('netconnections');
	$DB->Execute("INSERT INTO netlinkassingments VALUES (?,?,?)",array(NULL,$connid,$link['id']));
}
echo 'Zaimportowano '.count($netlinks).' połączeń między urządzeniami<BR>';


// *******************************************************************
// Sprawdzenie czy istnieje juz netnode dla podanej lokalizacji
// *******************************************************************
function check_location($location) {
	global $DB;
	$location['coowner']=NULL;
	if (isset($location['location_city'])) {
		$loc="location_city=".$location['location_city']." AND ";
		$loc.=$location['location_street']=='' ? "location_street IS NULL" : "location_street=".$location['location_street'];
		$loc.=" AND location_house='".$location['location_house']."' AND ";
		$loc.=$location['location_flat']=='' ? "(location_flat IS NULL or location_flat='')" : "location_flat='".$location['location_flat']."'";
	} elseif ($location['location']<>'') {
		$loc=" location='".$location['location']."'";
	} else {
		$loc=" name='".$location['devname']."'";
		echo '<LI><FONT COLOR="red">Błąd TERYT dla '.$location['devname'].'</FONT></LI>';
	}
	$netnodes=$DB->GetAll("SELECT * FROM netnodes WHERE ".$loc);
	if (count($netnodes)) { 
		echo '<LI><FONT COLOR="green">Znaleziono węzeł "'.$netnodes[0]['name'].'"</FONT></LI>';
		$location['netnodeid']=$netnodes[0]['id'];
	} else {
		$nodes=$DB->GetAll("SELECT * FROM nodes WHERE ((".$loc.") OR netdev=".$location['devid'].") AND ownerid>0 GROUP BY ownerid");
		$ilosc=count($nodes);
		if ($ilosc==1) {
			$location['type']=2;
			$location['ownership']=3;
			$location['ownerid']=$nodes[0]['ownerid'];
			echo '<LI><FONT COLOR="violet">Węzeł kliencki ['.$nodes[0]['ownerid'].']</FONT></LI>';
		} else {
			$location['type']=4;
			$location['ownership']=0;
			$location['ownerid']=NULL;
			echo '<LI><FONT COLOR="blue">Węzeł backbone ['.$ilosc.']</FONT></LI>';
		}

	}
	return($location);
}

// *******************************************************************
// Dodawanie do netnodes na podstawie rekordu z netdevices_old
// *******************************************************************
function netnodeadd($location) {
	global $DB;
	$city=$DB->GetAll("SELECT * FROM location_cities WHERE id=".$location['location_city']);
	$name=$city[0]['name'];
	if ($name=='') {
		if ($location['location']<>'')
			$name=$location['location'];
		else
			$name=$location['devname'];
	} else {
		if ($location['location_street']!='') {
			$street=$DB->GetAll("SELECT * FROM location_streets WHERE id=".$location['location_street']);
			$type=$DB->GetOne("SELECT name FROM location_street_types WHERE id=".$street[0]['typeid']);
			$street1=$street[0]['name2'] ? ' '.$street[0]['name2'] : '';
			$name.=', '.$type.$street1.' '.$street[0]['name'];
		}
		$name.=' '.preg_replace('/ /','',$location['location_house']);
		$name.=$location['location_flat'] ? '/'.$location['location_flat'] : '';
	}
	$args = array('name'=>$name,
		'type'=>$location['type'],
		'status'=>$location['status'],
		'location' => $location['location'],
		'location_city' => $location['location_city'] ? $location['location_city'] : NULL,
		'location_street' => $location['location_street'] ? $location['location_street'] : NULL,
		'location_house' => $location['location_house'] ? $location['location_house'] : NULL,
		'location_flat' => $location['location_flat'] ? $location['location_flat'] : NULL,
		'longitude' => !empty($location['longitude']) ? $location['longitude'] : NULL,
		'latitude' => !empty($location['latitude']) ? $location['latitude'] : NULL,
		'ownership'=>$location['ownership'],
		'ownerid'=>$location['ownerid'],
		'coowner'=>NULL,
		'uip'=>0,
		'miar'=>0,
		'divisionid' => !empty($location['divisionid']) ? $location['divisionid'] : NULL,
		'invprojectid' => !empty($location['invprojectid']) ? $location['invprojectid'] : NULL
	);

        $DB->Execute("INSERT INTO netnodes (name,type,status,location,location_city,location_street,location_house,location_flat,longitude,latitude,ownership,ownerid,coowner,uip,miar,divisionid,invprojectid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array_values($args));
 	#echo '<PRE>';var_dump($DB->GetErrors());echo '</PRE>';
        $netnodeid = $DB->GetLastInsertID('netnodes');
	echo '<LI><FONT COLOR="green">Utworzono węzeł "'.$args['name'].'"</FONT></LI>';
	return($netnodeid);
}

?>
