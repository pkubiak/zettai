<?php 
require_once('inc/init.php');
require_once('inc/parse_gpx.php');
global $db;

$method = $_GET["method"];
$id = $_GET["id"];

if($method=='gpx' && isset($id) && isset($_FILES['userfile']['tmp_name']))
{
	$uploaddir = $cfg['uploadpath'].'gpxes/';
	$uploadfile = $uploaddir.basename($_FILES['userfile']['name']);
	
	$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
	if($ext != 'gpx') {
		echo '<script>parent.loadGpxFailed(\'Bad file format\');</script>';
		return;
	}
	move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);
	$x = parse($uploadfile);
	$y = array();
	foreach($x as $t)
	{
		$y[]='('.$t["lat"];
		$y[]=$t["lon"].')';
	}
	
	$string = implode(',',$y);
	$query = $db->prepare("INSERT INTO paths values(default, :id, PATH(:pnt),'Path imported from GPX','',DEFAULT, DEFAULT) returning id");
	$query->bindValue(':id', (int)$id, PDO::PARAM_INT);
	$query->bindValue(':pnt', $string, PDO::PARAM_STR);
	//echo $string;
	if(!$query->execute()){
		$arr = $db->errorInfo();
		echo '<script>parent.loadGpxFailed(\'.$db->Select map doesnt exists\');</script>';//echo 'Mapa o podanym id nie istnieje';
	}else
	{
		$string = '<small>Path imported: '.strftime('%F %T').'</small><hr class="dashed"/>';
		$id = $query->fetchAll();
		$dist = path_length($x);
		$query = $db->prepare("INSERT INTO stats values(?,'dist',?)");
		$query->execute(array($id[0]["id"],$dist));
		$last = end($x);
		$string.='<b>Total distance</b>: '.round($dist, 2).'km<br/>';
		
		$ts = strptime($last["time"],'%FT%TZ');
		$ts = mktime($ts['tm_hour'], $ts['tm_min'], $ts['tm_sec'], $ts['tm_mon'], $ts['tm_mday'], ($ts['tm_year'] + 1900));
		$tx = strptime($x[0]["time"],'%FT%TZ');
		$tx = mktime($tx['tm_hour'], $tx['tm_min'], $tx['tm_sec'], $tx['tm_mon'], $tx['tm_mday'], ($tx['tm_year'] + 1900));
		
		
		$query = $db->prepare("INSERT INTO stats values(?,'time',?)");
		$query->execute(array($id[0]["id"],$ts - $tx));
		$v = $ts - $tx;
		$string.='<b>Total time</b>: '.(int)($v/3600).'h '.(int)(($v%3600)/60).'m '.(int)($v%60).'s'.'<br/>';
		
		$query = $db->prepare("INSERT INTO stats values(?,'avg',?)");
		$query->execute(array($id[0]["id"],$dist * 3600 /($ts - $tx)));
		$string.='<b>Avg. speed</b>: '.round($dist*3600.0/($v),2).'km/h'.'<br/>';
		
		
		$ele = max_min_ele($x);
		$query = $db->prepare("INSERT INTO stats values(?,'max',?)");
		$query->execute(array($id[0]["id"],$ele['max']));
		$string.='<b>Max elevation</b>: '.round($ele['max'],2).'m'.'<br/>';
		
		$query = $db->prepare("INSERT INTO stats values(?,'min',?)");
		$query->execute(array($id[0]["id"],$ele['min']));
		$string.='<b>Min elevation</b>: '.round($ele['min'],2).'m'.'<br/>';
		
		//$query = $db->prepare("select concat(name,': ',value) from stats where id = ?");
		//$query->execute(array($id[0]["id"]));
		//$s = $query->fetchAll();
		//$string ='';
		//foreach($s as $f) $string = $string.$f["concat"]."\n";
		$query = $db->prepare("update paths set description = ? where id = ? ");
		$query->execute(array($string,$id[0]["id"]));
		
		echo '<script>parent.loadGpxSuccess();</script>';
	}
}

if($method=='img' && isset($_FILES['userfile']['tmp_name']))
{
	$uploaddir = '/home/zzztn301/public_html/photos/';
	$uploadfile = $uploaddir.basename($_FILES['userfile']['name']);
	
	$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
	if($ext != 'jpg' && $ext!='bmp' && $ext!=png) {
		echo 'Zly fomat zdjecia';
		return;
	}
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		
		$filename = $uploadfile;
		$const = 64;
		
		// Get new sizes
		list($width, $height) = getimagesize($filename);
		$newwidth = $width > $const ? $const:$width;
		$newheight = ($newwidth/$width)*$height;
		
		// Load
		$thumb = imagecreatetruecolor($newwidth, $newheight);
		$source = imagecreatefromjpeg($filename);

		// Resize
		imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		ob_start("imagejpeg");
		imagejpeg($thumb,null,92);
		$string= ob_get_contents();
		ob_end_clean();
		$local = fopen('data://text/plain,' . urlencode($string),'rb');
		
		$db->beginTransaction();
		$oid = $db->pgsqlLOBCreate();
		$stream = $db->pgsqlLOBOpen($oid, 'w');

		stream_copy_to_stream($local, $stream);
		$local = null;
		$stream = null;
		//$stmt = $db->prepare("INSERT INTO photos VALUES (default, ?,?,'',current_timestamp,current_timestamp,?)");
		$stmt = $db->prepare("INSERT INTO photos VALUES (default, ?,?,'',current_timestamp,current_timestamp,1)");
		//$stmt->execute(array($oid,$filename,$_SESSION["id"]));
		$stmt->execute(array($oid,$filename));
		$db->commit();
		
		// $db->beginTransaction();
		// $stmt = $db->prepare("select thumbnail from photos where url = ? limit 1");
		// $stmt->execute(array($filename));
		// $stmt->bindColumn('thumbnail', $lob, PDO::PARAM_LOB);
		// $stmt->fetch(PDO::FETCH_BOUND);
		// $oid = $db->pgsqlLOBOpen($lob, 'r');
		// header('Content-type: image/jpg');
		// fpassthru($oid);
		
	} else {
		echo "Possible file upload attack!\n";
	}
}
if($method=='img' || ($method=='gpx' && isset($id))):
?>
<html>
<body style="overflow:hidden;margin:0;">
<form enctype="multipart/form-data" action="" method="POST" id="formatka">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000000" />
    <!-- Name of input element determines name in $_FILES array -->
	<input name="userfile" type="file" />
 <!-- <input type="submit" value="Send File" />-->
</form>

</body>
</html>
<?php
endif;
?>
