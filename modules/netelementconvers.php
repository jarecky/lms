<?

// *******************************************************************
// Tworzymy rekordy dla urządzeń które nie mają odniesień do netnodes
// Etap 1. - dla pełnych rekordów TERYT
// *******************************************************************
$devices=$DB->GetAll("SELECT * FROM netdevices_old where netnodeid is NULL and location_city is not null group by location_city,location_street,location_house,location_flat");
if (is_array($devices)) foreach ($devices AS $dev) {
	echo $dev['id'].' '.$dev['name'].': ';
	#echo '<PRE>';print_r($dev);echo '</PRE>';
	$loc="location_city=".$dev['location_city'];
	$loc.=" AND ";
        $loc.=$dev['location_street']=='' ? "location_street IS NULL" : "location_street=".$dev['location_street'];
	$loc.=" AND ";
	$loc.="location_house='".$dev['location_house']."'";
	$loc.=" AND ";
	$loc.=$dev['location_flat']=='' ? "(location_flat IS NULL or location_flat='')" : "location_flat='".$dev['location_flat']."'";
	$netnodes=$DB->GetOne("SELECT id FROM netnodes WHERE ".$loc);
	if (is_array($netnodes)) { 
		$netnode=$netnodes[0];
		$DB->Execute("UPDATE netdevices_old SET netnodeid=".$netnode['id']." WHERE $loc");
		echo ' <FONT COLOR="green">przypisany do węzła '.$netnode['name'].'</FONT>';
	} else {
		#echo '<BR>"'.$loc.'"<BR>';
		$nodes=$DB->GetAll("SELECT * FROM nodes WHERE ((".$loc.") OR netdev=".$dev['id'].") AND ownerid>0 GROUP BY ownerid");
		$ilosc=count($nodes);
		if ($ilosc==1) {
			$dev['type']=2;
			$dev['ownership']=3;
			$dev['ownerid']=$nodes[0]['ownerid'];
			$netnodeid=netnodeadd($dev,$loc);
			if ($netnodeid>0) 
				$DB->Execute("UPDATE netdevices_old SET netnodeid=$netnodeid WHERE $loc");
			else 
				echo '<FONT COLOR="red">Błąd dodawania węzła dla '.$dev['name'].'</FONT> ';
			echo '<FONT COLOR="green">Węzeł kliencki ['.$nodes[0]['ownerid'].']</FONT> ';
		} else {
			$dev['type']=4;
			$dev['ownership']=0;
			$dev['ownerid']=NULL;
			$netnodeid=netnodeadd($dev,$loc);
			if ($netnodeid>0)
				$DB->Execute("UPDATE netdevices_old SET netnodeid=$netnodeid WHERE $loc");
			else
				echo '<FONT COLOR="red">Błąd dodawania węzła dla '.$dev['name'].'</FONT> ';
			echo '<FONT COLOR="blue">Węzeł backbone ['.$ilosc.']</FONT>';
		}
	}
	echo '<BR>';
}
// *******************************************************************
// Tworzymy rekordy dla urządzeń które nie mają odniesień do netnodes
// Etap 2. - dla urządzeń bez poprawnego TERYT
// *******************************************************************
$devices=$DB->GetAll("SELECT * FROM netdevices_old where netnodeid is NULL group by location");
if (is_array($devices)) foreach ($devices AS $dev) {
	echo $dev['id'].' '.$dev['name'].': '.$dev['location'];
	$loc=" location='".$dev['location']."'";
        $netnodes=$DB->GetOne("SELECT id FROM netnodes WHERE ".$loc);
        if (is_array($netnodes)) {
                $netnode=$netnodes[0];
                $DB->Execute("UPDATE netdevices_old SET netnodeid=".$netnode['id']." WHERE $loc");
                echo ' <FONT COLOR="green">przypisany do węzła '.$netnode['name'].'</FONT>';
        } else {
                $nodes=$DB->GetAll("SELECT * FROM nodes WHERE ((".$loc.") OR netdev=".$dev['id'].") AND ownerid>0 GROUP BY ownerid");
                $ilosc=count($nodes);
                if ($ilosc==1) {
                        $dev['type']=2;
                        $dev['ownership']=3;
                        $dev['ownerid']=$nodes[0]['ownerid'];
                        $netnodeid=netnodeadd($dev,$loc);
                        if ($netnodeid>0)
                                $DB->Execute("UPDATE netdevices_old SET netnodeid=$netnodeid WHERE $loc");
                        else
                                echo '<FONT COLOR="red">Błąd dodawania węzła dla '.$dev['name'].'</FONT> ';
                        echo '<FONT COLOR="green">Węzeł kliencki ['.$nodes[0]['ownerid'].']</FONT> ';
                } else {
                        $dev['type']=4;
                        $dev['ownership']=0;
                        $dev['ownerid']=NULL;
                        $netnodeid=netnodeadd($dev,$loc);
                        if ($netnodeid>0)
                                $DB->Execute("UPDATE netdevices_old SET netnodeid=$netnodeid WHERE $loc");
                        else
                                echo '<FONT COLOR="red">Błąd dodawania węzła dla '.$dev['name'].'</FONT> ';
                        echo '<FONT COLOR="blue">Węzeł backbone ['.$ilosc.']</FONT>';
                }
	}	
	echo '<BR>';
}

// *******************************************************************
// Przeniesienie informacji u urządzeniach
// Etap 1. - przeniesienie danych z netdevices_old do netelements
// *******************************************************************

$devices=$DB->GetAll("SELECT * FROM netdevices_old ORDER BY id ASC");
if (is_array($devices)) foreach ($devices AS $dev) {
	$check=$DB->GetOne("SELECT id FROM netelements WHERE id=".$dev['id']);
	if (!isset($check)) {
		echo $dev['name'].' '.$dev['producer'].' '.$dev['model'].' ';
		$DB->Execute("INSERT INTO netelements VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",array($dev['id'],$dev['name'],0,$dev['description'],$dev['producer'],$dev['model'],$dev['serialnumber'],$dev['purchasetime'],$dev['guaranteeperiod'],$dev['netnodeid'],$dev['invprojectid'],$dev['netdevicemodelid'],$dev['status']));
		$DB->Execute("INSERT INTO netdevices VALUES (?,?,?,?,?,?,?,?,?)",array(null,$dev['id'],$dev['shortname'],$dev['nastype'],$dev['clients'],'',$dev['secret'],$dev['community'],$dev['channelid']));
		echo '<font color="green">Ilość portów: '.$dev['ports'].'</font> ';
		$tech=array();
		$nls=$DB->GetAll("SELECT * FROM netlinks WHERE src=".$dev['id']." OR dst=".$dev['id']);
		$copper=array();
		if (is_array($nls)) foreach ($nls AS $ls) {
			if ($ls['technology']>=100 and $ls['technology']<200) {
				if ($ls['src']==$dev['id']) {
					if ($ls['srcradiosector']) {
						$tech[$ls['technology']][$ls['srcradiosector']]=1;
					} else {
						$tech[$ls['technology']][0]++;
					}
				} else {
					if ($ls['dstradiosector']) {
						$tech[$ls['technology']][$ls['dstradiosector']]=1;
					} else {
						$tech[$ls['technology']][0]++;
					}
				}
			} else {
				if ($ls['src']==$dev['id']) {
					$tech[$ls['technology']][$ls['dstport']]++;
					if ($ls['dstport']) {
						if ($ls['technology']<100) {
							$copper[$ls['dstport']]=1;
						} else {
							$fiber[$ls['dstport']]=1;
						}
					}
				} else {
					$tech[$ls['technology']][$ls['srcport']]++;
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
		$nps=$DB->GetAll("SELECT * FROM nodes WHERE netdev=".$dev['id']);
		if (is_array($nps)) foreach ($nps AS $np) {
			if ($np['linktechnology']>=100 and $np['linktechnology']<200) {
				if ($np['linkradiosector'])
					$tech[$np['linktechnology']][$np['linkradiosector']]=1;
				else
					$tech[$np['linktechnology']][0]++;
			} else {
				$tech[$np['linktechnology']][$np['port']]++;
				if ($np['port']) {
					if ($np['linktechnology']<100) {
						$copper[$np['port']]=1;
					} else {
						$fiber[$np['port']]=1;
					}
				}

			}
		}
		#echo '<PRE>';print_r($tech);echo '</PRE>';
		#echo '<PRE>';print_r($copper);echo '</PRE>';
		$ports=array();
		$wport_num=1; $cport_num=1; $fport_num=1;
		if (is_array($tech)) foreach ($tech AS $t => $p ) {
			if ($t<100) {
				foreach ($p AS $nr => $i) {
					if (preg_match('/^([0-9]+),?/',$NETTECHNOLOGIES[$t]['connector'],$c))
						$connector=$c[1];
					for ($x=1;$x<=$i;$x++) {
						if ($nr) {
							$cport_num=$nr;
						} else {
							for (true;isset($copper[$cport_num]);$cport_num++);
						}
						$port=array(
							'id' 		=> NULL,
							'netelemid'	=> $dev['id'],
							'type'		=> 0,
							'label'		=> 'copper'.$cport_num++,
							'connectortype'	=> $connector,
							'technology'	=> $t,
							'capacity'	=> 1
						);
						$ports[]=$port;
					}
				}
			} elseif ($t<200) {
				foreach ($p AS $rs => $i) {
					if ($rs>0) {
						$rss=$DB->GetAll("SELECT * FROM netradiosectors_old WHERE id=".$rs);
						$rss=$rss[0];
						$rs=array(
							'id'		=> NULL,
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
                                                        'type'          => 2,
                                                        'label'         => 'wireless'.$wport_num++,
                                                        'connectortype' => 0,
                                                        'technology'    => $t,
                                                        'capacity'      => 100,
							'radiosector'	=> $rs
                                                );
						$ports[]=$port;

					} else { 
						for ($x=1;$x<=$i;$x++) {
							$port=array(
								'id'            => NULL,
								'netelemid'     => $dev['id'],
								'type'          => 2,
								'label'         => 'wireless'.$wport_num++,
								'connectortype' => 0,
								'technology'    => $t,
								'capacity'      => 1
							);
							$ports[]=$port;
						}
					}
				}

			} elseif ($t<300) {
                                foreach ($p AS $nr => $i) {
                                        if (preg_match('/^([0-9]+),?/',$NETTECHNOLOGIES[$t]['connector'],$c))
                                                $connector=$c[1];
                                        for ($x=1;$x<=$i;$x++) {
                                                if ($nr) {
                                                        $fport_num=$nr;
                                                } else {
                                                        for (true;isset($fiber[$fport_num]);$fport_num++);
                                                }
                                                $port=array(
                                                        'id'            => NULL,
                                                        'netelemid'     => $dev['id'],
                                                        'type'          => 0,
                                                        'label'         => 'copper'.$fport_num++,
                                                        'connectortype' => $connector,
                                                        'technology'    => $t,
                                                        'capacity'      => 1
                                                );
                                                $ports[]=$port;
                                        }
                                }	
			} else {
				echo '<FONT color="red">Nieznana technologia '.$t.'</FONT><BR>';
			}
		}
		#echo '<PRE>';print_r($ports);echo '</PRE>';
		foreach ($ports AS $port) {
			unset($radiosector);
			if (isset($port['radiosector'])) {
				$radiosector=$port['radiosector'];
				unset($port['radiosector']);
			}
			echo '<FONT COLOR="blue">'.$port['label'].'</FONT> ';
			$DB->Execute("INSERT INTO netports VALUES (?,?,?,?,?,?,?)",array_values($port));
			if (isset($radiosector)) {
				$radiosector['netportid']=$DB->GetLastInsertID('netports');
				$DB->Execute("INSERT INTO netradiosectors VALUES (?,?,?,?,?,?,?,?,?,?,?)",array_values($radiosector));
				echo '<FONT COLOR="violet">['.$radiosector['name'].'] </FONT>';
			}
		}
		echo '<BR>';
	} else {
		echo '<FONT COLOR="red">Urządzenie '.$dev['name'].' juz zaimportowane!</FONT><BR>';
	}
}



// *******************************************************************
// Dodawanie do netnodes na podstawie rekordu z netdevices_old
// *******************************************************************
function netnodeadd($dev,$location) {
	global $DB;
	$city=$DB->GetAll("SELECT * FROM location_cities WHERE id=".$dev['location_city']);
	$name=$city[0]['name'];
	if ($name=='') {
		$dev['name']=$dev['location'];
	} else {
		if ($dev['location_street']!='') {
			$street=$DB->GetAll("SELECT * FROM location_streets WHERE id=".$dev['location_street']);
			$type=$DB->GetOne("SELECT name FROM location_street_types WHERE id=".$street[0]['typeid']);
			$street1=$street[0]['name2'] ? ' '.$street[0]['name2'] : '';
			$name.=', '.$type.$street1.' '.$street[0]['name'];
		}
		$name.=' '.preg_replace('/ /','',$dev['location_house']);
		$name.=$dev['location_flat'] ? '/'.$dev['location_flat'] : '';
		$dev['name']=$name;
		#$dev['location']=$name;
	}
	$args = array('name'=>$dev['name'],
		'type'=>$dev['type'],
		'status'=>$dev['status'],
		'location' => $dev['location'],
		'location_city' => $dev['location_city'] ? $dev['location_city'] : NULL,
		'location_street' => $dev['location_street'] ? $dev['location_street'] : NULL,
		'location_house' => $dev['location_house'] ? $dev['location_house'] : NULL,
		'location_flat' => $dev['location_flat'] ? $dev['location_flat'] : NULL,
		'longitude' => !empty($dev['longitude']) ? $dev['longitude'] : NULL,
		'latitude' => !empty($dev['latitude']) ? $dev['latitude'] : NULL,
		'ownership'=>$dev['ownership'],
		'ownerid'=>$dev['ownerid'],
		'coowner'=>NULL,
		'uip'=>0,
		'miar'=>0,
		'divisionid' => NULL,
		'invprojectid' => !empty($dev['invprojectid']) ? $dev['invprojectid'] : NULL
	);

        $DB->Execute("INSERT INTO netnodes (name,type,status,location,location_city,location_street,location_house,location_flat,longitude,latitude,ownership,ownerid,coowner,uip,miar,divisionid,invprojectid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array_values($args));
 	#echo '<PRE>';var_dump($DB->GetErrors());echo '</PRE>';
        $netnodeid = $DB->GetLastInsertID('netnodes');
	return($netnodeid);
}

?>
