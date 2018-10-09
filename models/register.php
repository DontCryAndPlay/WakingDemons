<?php
class Register {
	//TODO: country detection
	private $db;
	function __construct() {
		global $db;
		$this->db = $db;
	}
	public function newUser($data) {
		$data['password'] = crypt($data['password'], "$2y$".Core::$instance->config['algoDifficult']."$".Core::$instance->salt());
		if($this->db->query("INSERT INTO users (email,gender,lastactivity,password,registerdate) VALUES ('?','?',NOW(),'?',NOW())",$data['email'],$data['gender'],$data['password'])) {
			$dat = $this->db->query("SELECT id FROM users WHERE email='?' LIMIT 1", $data['email']);
			$uid = $dat->id;
			return $this->db_query("INSERT INTO userdata (id,firstname,lastname) VALUES ('?','?','?')", $uid, $data['fistname'], $data['lastname'])
		} else return false;
	}
	public function emailExists($email) {
		$dat = $this->db->query("SELECT count(id) as n FROM users WHERE email='?' LIMIT 1", $email);
		return $dat->n > 0;
	}
}
?>