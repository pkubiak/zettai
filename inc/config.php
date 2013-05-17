<?php
	$cfg = array();
	$cfg['db'] = 'pgsql:dbname=zzzpkp01;user=zzzpkp01;password=4YjwuuEdzd7K;host=db.tcs.uj.edu.pl';
	//$cfg['dbpfx'] = '
	
	$cfg['name'] = 'name';
	$cfg['uploadpath'] = '/home/zzzpkp01/public_html/zettai/uploads/';
	
	$cfg['recaptcha-private'] = '6Lf_vNsSAAAAABO6IldZE-mzv1zAbLDJHNHjQhsk';
	
	$db = new PDO($cfg['db']);
?>
