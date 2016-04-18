<?

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
