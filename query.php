<?php
require_once('inc/init.php');

//session_start();
error_reporting(E_ALL);

function do_welcome(){
	if(isset($_SESSION['username'])){//Zalogowany
		return array(
			'status'	=> 'SUCCESS',
			'login'		=> $_SESSION['username'], 
			'time'		=> time()
		);
	}else{//Niezalogowany
		return do_error('Unknown');
	}
}

/*function do_search_maps(){
	global $db;
	if(!isset($_POST['keyword']))
		return do_error('Malformed request');

	$keyword = $_POST['keyword'];
	$page = (isset($_POST['page'])&&preg_match('/^[0-9]{1,4}$/',$_POST['page'])?(int)$_POST['page']:0);
	$id = (isset($_SESSION['id'])?(int)$_SESSION['id']:-1);
	
	$r = array(
		'status'=>'SUCCESS',
		'results'=> array(),
		'page'=>$page,
	);
	
	$query = $db->prepare("SELECT * FROM maps WHERE (title ILIKE :title or description ILIKE :description) and (owner = :owner or public = true) ORDER BY 1 LIMIT 10 OFFSET :offset");
	$query->bindValue(':title', '%'.$keyword.'%', PDO::PARAM_STR);
	$query->bindValue(':description', '%'.$keyword.'%', PDO::PARAM_STR);
	$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
	$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	$query->execute();
	
	while($row = $query->fetch() ) {
		$r['results'][] = array(
			'id'=>$row['id'],
			'title'=>$row['title'],
			'desc'=>$row['description'],
			'type'=>'map'
		);
	}
	
	return $r;
}*/

function do_search_maps(){
	global $db;
	if(!isset($_POST['keyword']))
		return do_error('Malformed request');

	if(!isset($_POST['mask']))
		return do_error('Malformed request');
		
	$keyword = $_POST['keyword'];
	$page = (isset($_POST['page'])&&preg_match('/^[0-9]{1,4}$/',$_POST['page'])?(int)$_POST['page']:0);
	$id = (isset($_SESSION['id'])?(int)$_SESSION['id']:-1);
	$mask = $_POST['mask'];
	
	$r = array(
		'status'=>'SUCCESS',
		'results'=> array(),
		'page'=>$page,
	);
	
	if($mask == 1) {
		$query = $db->prepare("SELECT * FROM maps WHERE id in (select id from tags_maps where value ILIKE :tag ) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':tag', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 2) {
		$query = $db->prepare("SELECT * FROM maps WHERE (description ILIKE :description) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':description', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 3) {
		$query = $db->prepare("SELECT * FROM maps WHERE (id in (select id from tags_maps where value ILIKE :tag ) or description ILIKE :description) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':tag', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':description', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 4) {
		$query = $db->prepare("SELECT * FROM maps WHERE (title ILIKE :title) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':title', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 5) {
		$query = $db->prepare("SELECT * FROM maps WHERE (id in (select id from tags_maps where value ILIKE :tag ) or title ILIKE :title) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':tag', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':title', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 6) {
		$query = $db->prepare("SELECT * FROM maps WHERE (title ILIKE :title or description ILIKE :description) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':title', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':description', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	else if($mask == 7) {
		$query = $db->prepare("SELECT * FROM maps WHERE (id in (select id from tags_maps where value ILIKE :tag ) or title ILIKE :title or description ILIKE :description) and (owner = :owner or public = true) ORDER BY views DESC LIMIT 10 OFFSET :offset");
		$query->bindValue(':tag', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':title', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':description', '%'.$keyword.'%', PDO::PARAM_STR);
		$query->bindValue(':owner', (int)$id, PDO::PARAM_INT);
		$query->bindValue(':offset', (int)(10*$page), PDO::PARAM_INT);
	}
	
	if($mask != 0){
		$query->execute();
		
		while($row = $query->fetch() ) {
			$r['results'][] = array(
				'id'=>$row['id'],
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'type'=>'map'
			);
		}
	}
	
	return $r;
}


function do_login(){
	global $db;
	if(isset($_SESSION['username']))//Aktualnie zalogowany
		return do_error('Already logged!');
	
	if(!isset($_POST['password'])||!isset($_POST['username'])||!isset($_POST['rememberme']))//Niekompletne dane
		return do_error('Bad request');
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$password = hash('sha256', hash('sha256',$username).$password);
	
	$query = $db->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
	$query->execute(array($username, $password));
	$query = $query->fetchAll();
	
	if(count($query)==1){
		if($query[0]['blocked']==true)
			return do_error('User blocked :)');
		session_regenerate_id(true);
		$_SESSION['id'] = $query[0]['id'];
		$_SESSION['username'] = $username;
		
		if($_POST['rememberme']==='true')
			setSessionExpiration(7*24*60*60);//Zapamięta na 7 dni
	
		return array(
			'status'=>'SUCCESS'
		);
	}else{
		return do_error('Wrong username or password');
	}
	
}

function do_logout(){
	session_unset();
	session_destroy();
	session_regenerate_id();

	return array(
		'status'=>'SUCCESS'
	);
}

function recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}

function recaptcha_http_post($host, $path, $data, $port = 80) {
        $req = recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}

function recaptcha_check($privkey, $remoteip, $challenge, $response, $extra_params = array()){
	if ($privkey == null || $privkey == ''||$remoteip == null || $remoteip == '') 
		return false;

	//discard spam submissions
	if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0)
			return false;

	$response = recaptcha_http_post ('www.google.com', "/recaptcha/api/verify",
									  array (
											 'privatekey' => $privkey,
											 'remoteip' => $remoteip,
											 'challenge' => $challenge,
											 'response' => $response
											 ) + $extra_params
									  );

	$answers = explode ("\n", $response [1]);
	if (trim ($answers [0]) == 'true')
		return true;
	else
		return false;
}

function do_register(){
	global $db,$cfg;
	if(!isset($_POST['challenge'])||!isset($_POST['response'])||!isset($_POST['username'])||!isset($_POST['password'])||!isset($_POST['email']))
		return do_error('Wrong request');
	
	$challenge = $_POST['challenge'];
	$response = $_POST['response'];
	if(recaptcha_check($cfg['recaptcha-private'], $_SERVER['REMOTE_ADDR'], $challenge, $response)===false)
		return do_error('Wrong captcha');
	
	$username = $_POST['username'];
	
	if(!preg_match('/^[_0-9a-zA-Z]{3,24}$/',$username))
		return do_error('Wrong username');

	$query = $db->prepare("SELECT * FROM users WHERE login = ?");
	$query->execute(array($username));
	$query = $query->fetchAll();
	if(count($query)!=0)
		return do_error('Username already taken!');
	
	$email = $_POST['email'];
	//TODO: check email 	
	$query = $db->prepare("INSERT INTO users VALUES (DEFAULT, :username, :password, :email, NULL, false)");
	
	$password = hash('sha256', hash('sha256',$username).$_POST['password']);
	$query->bindValue(':username', $username, PDO::PARAM_STR);
	$query->bindValue(':password', $password, PDO::PARAM_STR);
	
	if($email==='')
		$query->bindValue(':email',null, PDO::PARAM_NULL);
	else
		$query->bindValue(':email', hash('sha256', $email));
		
	if($query->execute())
		return array("status"=>"SUCCESS");
	else
		return do_error('NieMaTakiejOpcji');
	
}

function do_get_maps_list(){
	global $db;
	if(isset($_SESSION['id'])){
		$query = $db->prepare('SELECT id, title, description, public, EXTRACT(EPOCH FROM CURRENT_TIMESTAMP-mtime) AS mtime FROM maps WHERE owner = :owner ORDER BY mtime');
		$query->bindValue(':owner',(int)$_SESSION['id'], PDO::PARAM_INT);
		$query->execute();
		
		$periods = array(
			array('last-hour',60*60),
			array('today',24*60*60),
			array('yesterday',2*24*60*60),
			array('last-week',7*24*60*60),
			array('last-month',31*24*60*60),
			array('last-year',365*24*60*60)
		);
		
		$q = array(
			'status'=>'SUCCESS',
			'maps'=>array(
				'older'=>array()
			)
		);
		foreach($periods as $v)
			$q['maps'][$v[0]] = array();
		
		
		while($row = $query->fetch()) {
			$p = 'older';
			for($i=count($periods)-1;$i>=0;$i--)
				if($periods[$i][1]>$row['mtime'])
					$p = $periods[$i][0];
				
				
			
			$q['maps'][$p][] = array(
				'id'=>$row['id'],
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'public'=>$row['public'],
				'mtime'=>$row['mtime']
			);
		}
		return $q;
	}else
		return do_error('Not Logged');
}

function update_tags($mid, $tags){
	global $db;
	
	$db->beginTransaction();
	$query = $db->prepare('DELETE FROM tags_maps WHERE id = :id');
	$query->bindValue(':id', $mid, PDO::PARAM_INT);
	$query->execute();
	
	$query = $db->prepare('INSERT INTO tags_maps VALUES(:id, :value)');
	foreach($tags as $t) {	
		$query->bindValue(':id', $mid , PDO::PARAM_INT);
		$query->bindValue(':value', $t, PDO::PARAM_STR);
		$query->execute();
	}
	
	if($db->commit()===false)
		return do_error('Error during tags creation');	
	
	return true;
}

function do_create_new_map(){
	global $db;
	//return array("status"=>"FAIL","key"=>gettype($_POST['tags']));
	if(isset($_SESSION['id'])){
		if(!isset($_POST['title'])||!isset($_POST['desc'])||!isset($_POST['policy'])||!in_array($_POST['policy'], array('private','public'))){
			//var_dump(!isset($_POST['title']),!isset($_POST['desc']),!isset($_POST['policy']),!in_array($_POST['policy'], array('private','public')),!isset($_POST['tags']));
			return do_error('Malformed request');
		}
		
		$query = $db->prepare('INSERT INTO maps VALUES(DEFAULT, :title, :description, :public, DEFAULT, DEFAULT, DEFAULT, :owner) RETURNING id');
		$query->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
		$query->bindValue(':description', $_POST['desc'], PDO::PARAM_STR);
		$query->bindValue(':public', ($_POST['policy']==='public'?true:false), PDO::PARAM_BOOL);
		$query->bindValue(':owner', $_SESSION['id'], PDO::PARAM_INT);
		
		if($query->execute()===false)
			return do_error('Unexpected error');
		else{
			$query = $query->fetchAll();
			if(count($query)!=1)
				return do_error('Unexpected error');
			else
				$id = (int)$query[0]['id'];
			
			if(($x=update_tags($id, (isset($_POST['tags'])?$_POST['tags']:array())))!==true)
				return $x;

			return array(
				'status'=>'SUCCESS',
				'mapid'=>$id
			);
		}
	}else
		return do_error('Not Logged');
}

function do_load_map(){
	global $db;
	if(!isset($_POST['mapid'])||!preg_match('/^[0-9]{1,7}$/',$_POST['mapid']))
		return do_error('Malformed request');
	$mapid = (int)$_POST['mapid'];
	
	$query = $db->prepare('SELECT a.title, a.description, a.public, a.views, to_char(ctime, \'YYYY-MM-DD HH24:MI:SS\') AS ctime, to_char(mtime, \'YYYY-MM-DD HH24:MI:SS\') AS mtime ,b.login AS author, (SELECT STRING_AGG(value,\',\' ORDER BY value) FROM tags_maps WHERE id=:id GROUP BY id) AS tags FROM maps a LEFT JOIN users b ON a.owner = b.id WHERE a.id = :id');
	$query->bindValue(':id', $mapid, PDO::PARAM_INT);
	if($query->execute()===true){
		$x = $query->fetchAll();
		if(count($x)==1){
			$query = $db->prepare('UPDATE maps SET views = views+1 WHERE id = :id');
			$query->bindValue(':id',$mapid, PDO::PARAM_INT);
			$query->execute();
			
			$q = array(
				'status'=>'SUCCESS',
				'mapid'=>$mapid,
				'title'=>$x[0]['title'],
				'description'=>$x[0]['description'],
				'public'=>$x[0]['public'],
				'views'=>$x[0]['views'],
				'ctime'=>$x[0]['ctime'],
				'mtime'=>$x[0]['mtime'],
				'author'=>$x[0]['author'],
				'tags'=>($x[0]['tags']===null?'':$x[0]['tags']),
				'objs'=>array()
			);
			
			$query = $db->prepare('SELECT id FROM pois WHERE mid = :id');
			$query->bindValue(':id', $mapid, PDO::PARAM_INT);
			$query->execute();
			
			while($row = $query->fetch()){
				$q['objs'][] = array('type'=>'POI', 'id'=>$row['id']);
			}
			
			$query = $db->prepare('SELECT id FROM paths WHERE mid = :id');
			$query->bindValue(':id', $mapid, PDO::PARAM_INT);
			$query->execute();
			
			while($row = $query->fetch()){
				$q['objs'][] = array('type'=>'PATH', 'id'=>$row['id']);
			}
			
			return $q;
		}
	}
	
	return array(
		'status'=>'FAIL',
		'error'=>'Access denied',
		'mapid'=>$mapid
	);
}

function do_get_pins_list(){
	global $db;
	$q = array(
		'status'=>'SUCCESS',
		'styles'=>array(),
		'pins'=>array(),
		'categories'=>array(),
	);
	
	$query = $db->prepare('SELECT name FROM pins_styles ORDER BY id');
	$query->execute();
	while(($row = $query->fetch()))
		$q['styles'][] = $row['name'];
	
	
	$query = $db->prepare('SELECT a.id, a.rank, b.category FROM pins_ordered a LEFT JOIN pins b ON a.id = b.id');//SELECT * FROM pins ORDER BY category, name ORDER BY name');
	$query->execute();
	while(($row=$query->fetch()))
		$q['pins'][] = array($row['id'], $row['category'], $row['rank']-1);
		
	$query = $db->prepare('SELECT id,name FROM pins_category ORDER BY corder');
	$query->execute();
	while(($row=$query->fetch()))
		$q['categories'][] = array('id'=>$row['id'], 'name'=>$row['name']);
	
	return $q;
}

/**
 * Do Delete map
 * 
 * @date 2013-01-22
 */
function do_delete_map(){
	global $db;
	if(isset($_SESSION['id'])){
		if(!isset($_POST['mapid'])||!preg_match('/^[0-9]{1,5}$/',$_POST['mapid']))
			return do_error('Malformed request');
		$mapid = (int)$_POST['mapid'];
		$query = $db->prepare('DELETE FROM maps WHERE id=:id AND owner = :owner');
		$query->bindValue(':id', $mapid, PDO::PARAM_INT);
		$query->bindValue(':owner', (int)$_SESSION['id'], PDO::PARAM_INT);
		if($query->execute()===false||$query->rowCount()==0)
			return do_error('Access denied');
		
		return array(
			'status'=>'SUCCESS',
		);
	}else
		return do_error('Not logged');
}

/**
 * Do Update map
 * 
 * @date 2013-01-22
 */
function do_update_map(){
	global $db;

	if(!isset($_SESSION['id']))
		return do_error('Not logged');
	else{
		if(!isset($_POST['title'])||!isset($_POST['desc'])||!isset($_POST['tags'])||!isset($_POST['policy'])||!isset($_POST['mapid'])||!preg_match('/^[0-9]{1,5}$/',$_POST['mapid']))
			return do_error('Malformed request');

		$query = $db->prepare('UPDATE maps SET title = :title, description = :desc, public = :public WHERE id = :id AND owner = :owner');
		$mapid = (int)$_POST['mapid'];
		$query->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
		$query->bindValue(':desc', $_POST['desc'], PDO::PARAM_STR);
		$query->bindValue(':public', ($_POST['policy']==='public'?true:false) , PDO::PARAM_BOOL);
		$query->bindValue(':id', $mapid, PDO::PARAM_INT);
		$query->bindValue(':owner', (int)$_SESSION['id'], PDO::PARAM_INT);
		
		if($query->execute()===false||$query->rowCount()==0)
			return do_error('Access denied');
		
		if(($x=update_tags($mapid, $_POST['tags']))!==true)
			return $x;
		
		return array(
			'status'=>'SUCCESS',
			'mapid'=>$mapid
		);
	}
}

/**
 * Get list of pois on given map
 * 
 * @date 2013-01-22
 */
function do_get_pois_list(){
	global $db;
	///TODO: SPRAWDZANIE UPRAWNIEŃ
	if(!isset($_POST['mapid'])||!isset($_POST['pois'])||!preg_match('/^[0-9]{1,5}$/',$_POST['mapid']))
		return do_error('Malformed request');
	else{
		$mapid = (int)$_POST['mapid'];
		
		$q = array(
			'status' => 'SUCCESS',
			'mapid' => $mapid,
			'pois' => array()
		);
		
		$pois = $_POST['pois'];
		$query = $db->prepare('SELECT a.id, a.title, a.description, a.position[0] AS lat, a.position[1] AS lng, b.name AS style, c.rank-1 AS pinid FROM pois a LEFT JOIN pins_styles b ON b.id = a.style LEFT JOIN pins_ordered c ON c.id = a.pinid WHERE a.mid = :mapid');
		$query->bindValue(':mapid', $mapid, PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('PIOTRUŚ :)');
			
		while($row=$query->fetch())
			if(in_array((int)$row['id'], $pois)){
				$q['pois'][$row['id']] = array(
					'title'=>$row['title'],
					'desc'=>$row['description'],
					'lat'=>(float)$row['lat'],
					'lng'=>(float)$row['lng'],
					'look'=>array(
						'type'=>'pin',
						'style'=>$row['style'],
						'pin'=>$row['pinid']
					)
				);
			}
		return $q;
	}
}

function do_get_paths_list(){
	global $db;

	if(!isset($_POST['mapid'])||!isset($_POST['paths']))
		return do_error('Malformed request');
	
	$mapid = (int)$_POST['mapid'];
	$q = array(
		'status' => 'SUCCESS',
		'mapid' => $mapid,
		'paths' => array()
	);
	
	$query = $db->prepare('SELECT * FROM paths WHERE mid = :mapid');
	$query->bindValue(':mapid', $mapid, PDO::PARAM_INT);
	if($query->execute()===false)
		return do_error('Unexpected error 5');
	
	while($row=$query->fetch())
		if(in_array((int)$row['id'], $_POST['paths'])){
			$q['paths'][$row['id']] = array(
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'points'=>$row['points']
				/*'look'=>array(
					'type'=>'pin',
					'style'=>$row['style'],
					'pin'=>$row['pinid']
				)*/
			);
		}
	return $q;
}

function do_update_poi_position(){
	global $db;
	if(!isset($_SESSION['id']))
		return do_error('Not logged');
	else{
		if(!isset($_POST['mapid'])||!isset($_POST['poi'])||!isset($_POST['lat'])||!isset($_POST['lng'])||
			!is_numeric($_POST['mapid'])||!is_numeric($_POST['poi'])||!is_numeric($_POST['lat'])||!is_numeric($_POST['lng']))
			return do_error('Malformed request');
			
		$mid = (int)$_POST['mapid'];
		$poi = (int)$_POST['poi'];
		
		$query = $db->prepare('UPDATE pois SET position = POINT(:lat, :lng) WHERE mid = :mid AND id = :poi');
		$query->bindValue(':lat', ''.$_POST['lat'], PDO::PARAM_STR);
		$query->bindValue(':lng', ''.$_POST['lng'], PDO::PARAM_STR);
		$query->bindValue(':mid', $mid, PDO::PARAM_INT);
		$query->bindValue(':poi', $poi, PDO::PARAM_INT);
		if($query->execute()===false||$query->rowCount()==0){
			$e = $db->errorInfo();
			return do_error($db->errorCode());
		}
		
		return array(
			'status'=>'success'
		);
	}	
}

function do_update_poi_pin(){
	global $db;
	if(!isset($_SESSION['id']))
		return do_error('Not logged');
	else{
		if(!isset($_POST['mapid'])||!isset($_POST['poiid'])||!isset($_POST['pin'])||!isset($_POST['style'])||
			!is_numeric($_POST['mapid'])||!is_numeric($_POST['poiid'])||!is_numeric($_POST['pin']))
			return do_error('Malformed request');
			
		$mid = (int)$_POST['mapid'];
		$poi = (int)$_POST['poiid'];
		
		$query = $db->prepare('UPDATE pois SET style = (SELECT id FROM pins_styles WHERE name = :style), pinid = :pin WHERE mid = :mid AND id = :poi');
		$query->bindValue(':style', ''.$_POST['style'], PDO::PARAM_STR);
		$query->bindValue(':pin', ''. (int)$_POST['pin'], PDO::PARAM_INT);
		$query->bindValue(':mid', $mid, PDO::PARAM_INT);
		$query->bindValue(':poi', $poi, PDO::PARAM_INT);
		
		if($query->execute()===false||$query->rowCount()==0){
			//$e = $db->errorInfo();
			return do_error('Unexpected error');
		}
		
		$id = $poi;
		
		$query =  $db->prepare('SELECT a.id, a.title, a.description, a.position[0] AS lat, a.position[1] AS lng, b.name AS style, c.rank-1 AS pinid FROM pois a LEFT JOIN pins_styles b ON b.id = a.style LEFT JOIN pins_ordered c ON c.id = a.pinid WHERE a.id = :id');
		$query->bindValue(':id', (int)$id, PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('Unexpected error 2');
	
		$row=$query->fetchAll();
		if(count($row)==0)
			return do_error('Unexpected error 3');
			
		$row = $row[0];
		return array(
			'status'=>'SUCCESS',
			'poiid'=>$id,
			'poi'=>array(
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'lat'=>(float)$row['lat'],
				'lng'=>(float)$row['lng'],
				'look'=>array(
					'type'=>'pin',
					'style'=>$row['style'],
					'pin'=>$row['pinid']
				)
			)
		);
	}	
}

function do_create_new_poi(){
	global $db;
	if(!isset($_SESSION['id']))
		return do_errro('Not logged');
	else{
		///TODO: check users ownership
		if(!isset($_POST['mapid'])||!isset($_POST['lat'])||!isset($_POST['lng'])||
			!is_numeric($_POST['mapid'])||!is_numeric($_POST['lat'])||!is_numeric($_POST['lng']))
			return do_error('Malformed request');
		
		$query = $db->prepare('INSERT INTO pois VALUES(DEFAULT, :mapid, NULL, (SELECT id FROM pins WHERE name = \'star-3.png\'), (SELECT id FROM pins_styles LIMIT 1), POINT(:lat, :lng), \'New POI\', \'\', DEFAULT, DEFAULT) RETURNING id');
		
		$query->bindValue(':mapid', (int)$_POST['mapid'], PDO::PARAM_INT);
		$query->bindValue(':lat', $_POST['lat'], PDO::PARAM_STR);
		$query->bindValue(':lng', $_POST['lng'], PDO::PARAM_STR);
		
		if($query->execute()===false)
			return do_error('Unexpected error 1');
			
		$id = $query->fetchAll();
		$id = $id[0]['id'];
		
		//return do_error(''.$id);
		$query =  $db->prepare('SELECT a.id, a.title, a.description, a.position[0] AS lat, a.position[1] AS lng, b.name AS style, c.rank-1 AS pinid FROM pois a LEFT JOIN pins_styles b ON b.id = a.style LEFT JOIN pins_ordered c ON c.id = a.pinid WHERE a.id = :id');
		$query->bindValue(':id', (int)$id, PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('Unexpected error 2');
	
		$row=$query->fetchAll();
		if(count($row)==0)
			return do_error('Unexpected error 3');
			
		$row = $row[0];
		return array(
			'status'=>'SUCCESS',
			'poiid'=>$id,
			'poi'=>array(
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'lat'=>(float)$row['lat'],
				'lng'=>(float)$row['lng'],
				'look'=>array(
					'type'=>'pin',
					'style'=>$row['style'],
					'pin'=>$row['pinid']
				)
			)
		);
	}
}

function do_update_poi_data(){
global $db;
	if(!isset($_SESSION['id']))
		return do_errro('Not logged');
	else{
		///TODO: check users ownership
		if(!isset($_POST['mapid'])||!isset($_POST['poiid'])||!isset($_POST['title'])||!isset($_POST['desc'])||
			!is_numeric($_POST['mapid'])||!is_numeric($_POST['poiid']))
			return do_error('Malformed request');
				
		$query = $db->prepare('UPDATE pois SET title = :title, description = :desc WHERE mid = :mid AND id = :poiid');
		$query->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
		$query->bindValue(':desc', $_POST['desc'], PDO::PARAM_STR);
		$query->bindValue(':mid', (int)$_POST['mapid'], PDO::PARAM_INT);
		$query->bindValue(':poiid', (int)$_POST['poiid'], PDO::PARAM_INT);
		
		if($query->execute()===false)
			return do_error('Unexpected error 1');
			
		$id = (int)$_POST['poiid'];
		
		$query =  $db->prepare('SELECT a.id, a.title, a.description, a.position[0] AS lat, a.position[1] AS lng, b.name AS style, c.rank-1 AS pinid FROM pois a LEFT JOIN pins_styles b ON b.id = a.style LEFT JOIN pins_ordered c ON c.id = a.pinid WHERE a.id = :id');
		$query->bindValue(':id', (int)$id, PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('Unexpected error 2');
	
		$row=$query->fetchAll();
		if(count($row)==0)
			return do_error('Unexpected error 3');
			
		$row = $row[0];
		return array(
			'status'=>'SUCCESS',
			'poiid'=>$id,
			'poi'=>array(
				'title'=>$row['title'],
				'desc'=>$row['description'],
				'lat'=>(float)$row['lat'],
				'lng'=>(float)$row['lng'],
				'look'=>array(
					'type'=>'pin',
					'style'=>$row['style'],
					'pin'=>$row['pinid']
				)
			)
		);
	}	
}
function do_delete_poi(){
	global $db;

	if(!isset($_SESSION['id']))
		return do_error('Not logged');
	else{
		if(!isset($_POST['poiid'])||!is_numeric($_POST['poiid']))
			return do_error('Malformed request');
		
		$query = $db->prepare('DELETE FROM pois WHERE id = :id');
		$query->bindValue(':id', (int)$_POST['poiid'], PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('Unexpected error 4');
		return array(
			'status'=>'SUCCESS',
			'poiid'=>(int)$_POST['poiid']
		);
	}
}

function do_remove_path(){
	global $db;

	if(!isset($_SESSION['id']))
		return do_error('Not logged');
	else{
		if(!isset($_POST['pathid'])||!is_numeric($_POST['pathid']))
			return do_error('Malformed request');
		
		$query = $db->prepare('DELETE FROM paths WHERE id = :id');
		$query->bindValue(':id', (int)$_POST['pathid'], PDO::PARAM_INT);
		if($query->execute()===false)
			return do_error('Unexpected error 14');
		if($query->rowCount()==0)
			return do_error('Wrong path id');
			
		return array(
			'status'=>'SUCCESS',
			'pathid'=>(int)$_POST['pathid']
		);
	}	
}

/*function do_set_privilege(){
	global $db;
	if(!isset($_POST['username']))
		return do_error('Bad request');
	if(!isset($_POST['right']))
		return do_error('Bad request');
	if(!isset($_POST['map']))
		return do_error('Bad request');
	if(!isset($_POST['mode']))
		return do_error('Bad request');
		
	$username = $_POST['username'];
	$right = $_POST['right'];
	$map = $_POST['map'];
	$mode = $_POST['mode'];
	
	if($mode == 'add') {
		$query = $db->prepare("insert into privileges values(:username,(select id from rights where name = :right),:map)");
		$query->bindValue(':username', (int)$username, PDO::PARAM_INT);
		$query->bindValue(':right', $right, PDO::PARAM_STR);
		$query->bindValue(':map', (int)$map, PDO::PARAM_INT);
	}
	if($mode == 'del') {
		$query = $db->prepare("delete from privileges where user = :username and right = (select id from rights where name = :right) and map = :map)");
		$query->bindValue(':username', (int)$username, PDO::PARAM_INT);
		$query->bindValue(':right', $right, PDO::PARAM_STR);
		$query->bindValue(':map', (int)$map, PDO::PARAM_INT);
	}
	if($query->execute()){
	
	}	
	else
		return do_error('JakisBlad');
}*/

function do_error($error = 'Wrong method'){
	return array(
		'status'=>'FAIL',
		'error'=> $error
	);	
}

function do_request($method){
	switch($method){
		case 'welcome':
			return do_welcome();
		case 'search-maps':
			return do_search_maps();
		case 'login':
			return do_login();
		case 'logout':
			return do_logout();
		case 'register':
			return do_register();
		case 'get-maps-list':
			return do_get_maps_list();
		case 'create-new-map':
			return do_create_new_map();
		case 'load-map':
			return do_load_map();
		case 'get-pins-list':
			return do_get_pins_list();
		case 'delete-map':
			return do_delete_map();
		case 'update-map':
			return do_update_map();
		case 'get-pois-list':
			return do_get_pois_list();
		case 'update-poi-position':
			return do_update_poi_position();
		case 'create-new-poi':
			return do_create_new_poi();
		case 'delete-poi':
			return do_delete_poi();
		case 'get-paths-list':
			return do_get_paths_list();
		case 'update-poi-data':
			return do_update_poi_data();
		case 'update-poi-pin':
			return do_update_poi_pin();
		case 'remove-path':
			return do_remove_path();
		default:
			return do_error();
			
	}
}

$method = (isset($_POST['method'])?$_POST['method']:'');
$x = json_encode(do_request($method));
if($x==null||json_last_error()!=JSON_ERROR_NONE)
	echo '{"status":"FAIL", "error":"ERROR"}';
else
	echo $x;

?>
