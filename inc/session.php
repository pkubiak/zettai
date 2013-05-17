<?php
require_once('config.php');

class MySessionHandler
{
    private $savePath;
	private $db;
	public $expire;
	
	public function __construct($db){
		$this->db = $db;
		$this->expire = 1200;
	}

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
		$query = $this->db->prepare("SELECT data FROM sessions WHERE name = ? AND expire > CURRENT_TIMESTAMP");
		$query->execute(array($id));
		$x = $query->fetchAll();

		if(count($x)==1)
			return $x[0]['data'];
		else
			return '';
    }

    public function write($id, $data)
    {
		$query = $this->db->prepare('SELECT name FROM sessions WHERE name = ?');
		$query->execute(array($id));
		
		if(count($query->fetchAll())===1){//Jest w bazie
			$query = $this->db->prepare("UPDATE sessions SET data = ?, expire = GREATEST(CURRENT_TIMESTAMP+?, expire) WHERE name = ?");
			$query->execute(array($data,$this->expire,$id));
			
			//DEBUG
			file_put_contents('error.txt', 'A:'.$query->rowCount().'|'.$id.'|'.$data."\n", FILE_APPEND | LOCK_EX);
		}else{
			$query = $this->db->prepare("INSERT INTO sessions VALUES(?,?,CURRENT_TIMESTAMP + ?)");
			$query->execute(array($id, $data, $this->expire));
			
			//DEBUG
			file_put_contents('error.txt', 'B:'.$query->rowCount().'|'.$id.'|'.$data.'|'.$this->expire."\n", FILE_APPEND | LOCK_EX);
		}
		
        if($query->rowCount()===1) return true;
		else return false;
    }

    public function destroy($id)
    {
		$this->expire = 1200;
        $query = $this->db->prepare("DELETE FROM sessions WHERE name = ?");
		$query->execute(array($id));
        return true;
    }

    public function gc($maxlifetime)
    {
		$query = $this->db->prepare("DELETE FROM sessions WHERE expire < CURRENT_TIMESTAMP");
		$query->execute();
		
		//DEBUG
		file_put_contents('error.txt', "GC\n", FILE_APPEND | LOCK_EX);
        return true;
    }
}

function setSessionExpiration($expire){
	global $mySessionHandler;
	//session_id();
	//$query = $this->db->prepare("UPDATE sessions SET expire = CURRENT_TIMESTAMP + ? WHERE name = ?");
	//$query->execute(array($expire, session_id()));
	//return ($query->rowCount()===1);
	$mySessionHandler->expire = $expire;
}

$mySessionHandler = new MySessionHandler($db);
session_set_save_handler(
    array($mySessionHandler, 'open'),
    array($mySessionHandler, 'close'),
    array($mySessionHandler, 'read'),
    array($mySessionHandler, 'write'),
    array($mySessionHandler, 'destroy'),
    array($mySessionHandler, 'gc')
    );

register_shutdown_function('session_write_close');

session_start();

?>
