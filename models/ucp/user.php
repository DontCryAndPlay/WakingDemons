<?php
class User {
	private $db;
	private $seat;
	private $charid;
	function __construct() {
		global $db;
		$this->db = $db;
		$this->seat = new SEAT();
		$this->charid = $_SESSION['uid'];
	}
	public function fetchBackground() {
		return Core::$instance->config['UCPBG'] == null? "1.jpg" : Core::$instance->config['UCPBG'];
	}
	public function fetchSeat($category) {
		debug("Fetching data from SEAT...");
		switch($category) {
			case "CharacterInfo":
				$this->fetchSeatCharacterInfo();
				break;
		}
	}
	
	private function fetchSeatCharacterInfo() {
		if(isset($_SESSION['seat']['character']['cooldown']) && time() - $_SESSION['seat']['character']['cooldown'] < 0) return;
		debug("Fetching SEAT character information...");
		
		$data = $this->seat->fetch("/api/v1/character/info/".$this->charid);

		if(is_array($data)) {		
			foreach($data as $k=>$v) {
				if($k == "accountBalance") $v=number_format($v,2,",",".");
				$_SESSION['seat']['character'][$k] = $v;
			}
		}

		unset($data);
		$_SESSION['seat']['character']['cooldown'] = time()+60;
	}
}
?>