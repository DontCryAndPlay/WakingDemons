<?php
class Authenticator {
	private $options = array(
		'bcrypt' => array('cost' => 15),
		'argon2' => array('memory_cost' => 1<<17, 'time_cost' => 5, 'threads' => 4),
	);
	private $allowedMethods = array(
		'PAM' => false,
		'bcrypt' => PASSWORD_BCRYPT,
		//'argon2' => PASSWORD_ARGON2I,
	);
	private $authMethod = false;
	private $algo;
	function __construct($method=false) {
		$this->setMethod($method);
	}
	public function setMethod($method=false) {
		if(!$method) return false;
		if($method !== false && isset($this->allowedMethods[$method])) {
			$this->authMethod = $method;
			$this->algo = $this->allowedMethods[$method];
		}
	}
	public function authenticate($username, $password, $storedHash = false) {
		if($username == "" || $password == "") return false;
		switch($this->authMethod) {
			case 'PAM':
				return $this->PAM($username, $password);
				break;
			case 'bcrypt':
				return $this->verifyHash($password, $storedHash);
				break;
			case 'argon2':
				return $this->verifyHash($password, $storedHash);
				break;
			default:
				return false;
		}
	}
	public function needsRehash($storedHash) {
		if($storedHash == "" || !$this->algo) return false;
		return password_needs_rehash($storedHash, $this->algo, $this->options[$this->authMethod]);
	}
	public function createHash($password=false) {
		if(!$password) return false;
		switch ($this->authMethod) {
			case 'bcrypt':
				return password_hash($password, PASSWORD_BCRYPT, $this->options['bcrypt']);
				break;

			default:
				return false;
				break;
		}
	}
	private function verifyHash($password, $storedHash=false) {
		if(!$storedHash) return false;
		return password_verify($password, $storedHash);
	}
	private function PAM() {

	}
}
?>