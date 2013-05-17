<?php
function parse($file){
	$a = array();
	preg_match_all('/(<trkpt.*?trkpt>)/mis',file_get_contents($file),$a);
	
	$pat1a = '/lat="(.*?)"/i';
	$pat1b = '/lon="(.*?)"/i';
	$pat2 = '/<ele.*ele>/i';
	$pat3 = '/<time.*time>/i';
	
	$x = array();
	foreach ($a[0] as $text) {
		
		preg_match($pat1a,$text,$a1); 
		preg_match($pat1b,$text,$a2);
		
		preg_match($pat2,$text,$b); 
		$r2 =  preg_split('/[<>]/', $b[0]);
		
		preg_match($pat3,$text,$c); 
		$r3 =  preg_split('/[<>]/', $c[0]);
		
		$x[] = array(
			'lat'=>$a1[1],
			'lon'=>$a2[1],
			'ele'=>$r2[2],
			'time'=>$r3[2]
		);
	}
	return $x;
}

function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km') { 
	$theta = $longitude1 - $longitude2; 
	$distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
	$distance = acos($distance); 
	$distance = rad2deg($distance); 
	$distance = $distance * 60 * 1.1515; 
	switch($unit) { 
		case 'Mi': break; 
		case 'Km' : $distance = $distance * 1.609344; 
	} 
	return (round($distance,5)); 
}

function path_length($x)
{
	$result = 0;
	foreach($x as $y)
	{
		if(!isset($lon)) {
			$lat = $y["lat"];
			$lon = $y["lon"];
			continue;
		}
		$result += getDistance((float)$lat,(float)$lon,(float)$y["lat"],(float)$y["lon"]);
		$lat = $y["lat"];
		$lon = $y["lon"];
	}
	return $result;
}

function max_min_ele($x)
{
	$result = array();
	$result["max"] = -10000;
	$result["min"] = 10000;
	foreach($x as $y)
	{
		if(isset($y["ele"])) {
			if($y["ele"] > $result["max"]) $result["max"] = $y["ele"];
			if($y["ele"] < $result["min"]) $result["min"] = $y["ele"];
		}
	}
	if($result["max"] == -10000) $result["max"] = 0;
	if($result["min"] == 10000) $result["min"] = 0;
	return $result;
}

?>
