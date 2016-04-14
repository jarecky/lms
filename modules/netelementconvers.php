<?

$devices=$DB->GetAll("SELECT * FROM netdevices_old");

foreach ($devices AS $dev) {
	if ($dev['netnodeid']>0) {
		#echo "Jest netnode! ";
	} elseif ($dev['location']=='') {
		#echo 'Brak lokacji!';
	} else {
		echo $dev['id'].' '.$dev['name'].': ';
		$nodes=$DB->GetAll("SELECT * FROM nodes WHERE netdev=".$dev['id']." AND ownerid>0 GROUP BY ownerid");
		if (count($nodes)==1) {
			echo 'Węzeł kliencki ';
		} else {
			echo 'Węzeł backbone ';
		}
		echo "Lokacja: ".$dev['location']." ";
		echo '<BR>';
	}


}

?>
