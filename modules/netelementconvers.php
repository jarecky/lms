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

$devices=$DB->GetAll("SELECT * FROM netdevices_old WHERE name regexp '(ap|rt)$' ORDER BY id ASC LIMIT 0,10");
if (is_array($devices)) foreach ($devices AS $dev) {
	$check=$DB->GetOne("SELECT id FROM netelements WHERE name='".$dev['name']."'");
	if (!isset($check)) {
		echo $dev['name'].' '.$dev['producer'].' '.$dev['model'].'<BR>';
		#$DB->Execute("INSERT INTO netelements VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",array(null,$dev['name'],0,$dev['description'],$dev['producer'],$dev['model'],$dev['serialnumber'],$dev['purchasetime'],$dev['guaranteeperiod'],$dev['netnodeid'],$dev['invprojectid'],$dev['netdevicemodelid'],$dev['status']));
		#$netelemid = $DB->GetLastInsertID('netelements');
		#$DB->Execute("INSERT INTO netdevices VALUES (?,?,?,?,?,?,?,?,?)",array(null,$netelemid,$dev['shortname'],$dev['nastype'],$dev['clients'],'',$dev['secret'],$dev['community'],$dev['channelid']));
		echo '<font color="green">Ilość portów: '.$dev['ports'].'</font><BR>';
		$tech=array();
		$nls=$DB->GetAll("SELECT * FROM netlinks WHERE src=".$dev['id']." OR dst=".$dev['id']);
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
				} else {
					$tech[$ls['technology']][$ls['srcport']]++;
				}
			}
		}
		#echo '<PRE>';print_r($tech);echo '</PRE>';
		$ports=array();
		$wport_num=1;
		if (is_array($tech)) foreach ($tech AS $t => $p ) {
			if ($t<100) {
				foreach ($p AS $nr => $i) {
					if (preg_match('/^([0-9]+),?/',$NETTECHNOLOGIES[$t]['connector'],$c))
						$connector=$c[1];
					for ($x=1;$x<=$i;$x++) {
						$port=array(
							'id' 		=> NULL,
							'netelemid'	=> $netelemid,
							'type'		=> 0,
							'label'		=> $nr ? 'copper'.$nr : 'copper'.$i,
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
						$port=array(
                                                        'id'            => NULL,
                                                        'netelemid'     => $netelemid,
                                                        'type'          => 2,
                                                        'label'         => 'wireless'.$wport_num++,
                                                        'connectortype' => 0,
                                                        'technology'    => $t,
                                                        'capacity'      => 100,
							'radiosector'	=> $rss
                                                );
						$ports[]=$port;

					} else { 
						for ($x=1;$x<=$i;$x++) {
							$port=array(
								'id'            => NULL,
								'netelemid'     => $netelemid,
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
	
			} else {
				echo '<FONT color="red">Nieznana technologia '.$t.'</FONT><BR>';
			}
		}
		echo '<PRE>';print_r($ports);echo '</PRE>';
		echo '<BR>';
	} else {
		echo '<FONT COLOR="red">Urządzenie '.$dev['name'].' juz zaimportowane!<BR>';
	}
}




function netelementadd($dev) {


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
