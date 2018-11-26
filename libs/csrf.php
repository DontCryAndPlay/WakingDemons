<?php
class CSRFToken {
	function __construct() {
		if(!isset($_SESSION)) return false;
	}
	private function generator() {
		return base64_encode(bin2hex(openssl_random_pseudo_bytes(64)));
	}
	public function getToken() {
		$_SESSION['token'] = $this->generator();
		return $_SESSION['token'];
	}
	public function checkToken($token) {
		$token1 = $_SESSION['token'];
		if($token1 === false) return false;
		$this->invalidateToken();
		return $token == $token1;
	}
	private function invalidateToken() {
		$_SESSION['token'] = false;
	}
}
?>