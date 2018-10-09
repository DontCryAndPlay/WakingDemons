<?php
class Session {
	public static $instance;
	function __construct($sessionLength=false) {
		self::$instance = $this;
		$this->setConfig($sessionLength);
		if(!isset($_SESSION)) session_start();
	}
	private function setConfig($lifetime=false) {
		if(!$lifetime)
			$lifetime = Core::$instance->config['sessionLifeTime'];
		ini_set('session.gc_maxlifetime', Core::$instance->config['sessionLifeTime']); //Set max session lifetime
		ini_set('session.cookie_lifetime', $lifetime);
		ini_set('session.hash_bits_per_character', 6); //improved security for sessions
		session_save_path(Core::$instance->config['sessionPath']); //path to save the session files
		session_name("SID");
	}
	public function createUserSession() {
		session_unset();
		session_regenerate_id(true); //generate a new sessID and delete previous session file
		return true;
	}
	public function deleteUserSession() {
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		session_unset();
		session_regenerate_id(true);
		session_destroy();
		return true;
	}
}
?>