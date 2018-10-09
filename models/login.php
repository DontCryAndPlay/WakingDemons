<?php
require("libs/crest.eve.php");
class Login {
	private $db;
	function __construct() {
		global $db;
		$this->db = $db;
	}

	public function checkAccess($uid, $name) { //check if user exists and is granted to login
		$data = $this->db->query("SELECT count(id) as n, isAdmin FROM userInformation WHERE (id='?' or name='?') and accessGranted = 1  LIMIT 1", $uid, $name);

		if ( $data[0]->n > 0 ) {

			$_SESSION['uid'] = $uid;

			$crest = new Crest();
			$crest->setUri("/characters/" . $uid . "/");
			$crestData = $crest->doRequest();
			
			foreach($crestData as $k=>$v)
				$_SESSION['crest']['character'][$k] = $v;
			
			if($data[0]->isAdmin == true)
				$_SESSION['isAdmin'] = true;

			$data = $this->db->query("SELECT count(id) as n FROM corps WHERE id='?' LIMIT 1", $_SESSION['crest']['character']['corporation_id']);

			//keep corps table updated (according to crest data)
			$corpId = $_SESSION['crest']['character']['corporation_id'];
			$crest->setUri("/corporations/" . $corpId . "/");
			$corpData = $crest->doRequest();

			foreach($corpData as $k=>$v)
				$_SESSION['crest']['corporation'][$k] = $v;


			$this->db->query("INSERT INTO corps(id, name, ticker, alliance_id, ceo_id, member_count) VALUES ('?','?','?','?','?','?') ON DUPLICATE KEY UPDATE name=VALUES(name), ticker=VALUES(ticker), alliance_id=VALUES(alliance_id), ceo_id=VALUES(ceo_id), member_count=VALUES(member_count)", $corpId, $corpData['corporation_name'], $corpData['ticker'], $corpData['alliance_id'], $corpData['ceo_id'], $corpData['member_count']);

			//keep updated userInformation table
			$this->db->query("UPDATE userInformation SET name='?', corpid='?', gender='?', motto='?' WHERE id='?'", $name, $crestData['corporation_id'], $crestData['gender'], $crestData['description'], $uid);
			//Save the tokens
			$this->db->query("INSERT INTO tokens (uid, tokenType, cooldown, token) VALUES ('?', '?', '?', '?'), ('?', '?', '?', '?') ON DUPLICATE KEY UPDATE cooldown=VALUES(cooldown), token=VALUES(token)", $uid, "Bearer", $_SESSION['oauth']['cooldown'], $_SESSION['oauth']['token'], $uid, "refresh", 0, $_SESSION['oauth']['refreshToken']);
			return true; //finally, log into account.
		} else return false;
	}
}
?>