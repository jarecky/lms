<?

$devices=$DB->GetAll("SELECT * FROM netdevices_old");

foreach ($devices AS $dev) {
	echo $dev['id'].' '.$dev['name'].': ';
	if ($dev['netnodeid']>0) {
		echo "Jest netnode! ";
	} else {
		$nodes=$DB->GetAll("SELECT * FROM nodes WHERE netdev=".$dev['id']." AND ownerid>0");
		if (count($nodes)==0) {
			echo 'Backbone ';
		} else {
			echo 'Klient '.count($nodes).' komputer(ów) ';
		}
		echo "Brak netnode! ";
		if ($dev['location']!='') {
			echo "Lokacja: ".$dev['location']." ";
		} else {
			echo 'Brak lokacji!';
		}
	}


	echo '<BR>';
}

?>
