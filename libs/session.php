<?php
class Session {
	public static $instance;
	function __construct($sessionLength=false) {
		self::$instance = $this;
		if(!isset($_SESSION)) {
			$this->setConfig($sessionLength);
			session_start();
		}
	}
	private function setConfig($lifetime=false) {
		if(!$lifetime)
			$lifetime = Core::$instance->config['sessionLifeTime'];
		ini_set('session.gc_maxlifetime', Core::$instance->config['sessionLifeTime']); //Set max session lifetime
		ini_set('session.cookie_lifetime', $lifetime);
		debug("Session lifetime set to " . $lifetime);
		ini_set('session.hash_bits_per_character', 6); //improved security for sessions
		ini_set('session.sid_bits_per_character', 6);
		ini_set('session.sid_length', 128);
		$algo = "md5";
		$algos = hash_algos();
		
		if(in_array("sha512", $algos))
			$algo = "sha512";
		elseif(in_array("sha256", $algos))
			$algo = "sha256";
		elseif(in_array("sha1", $algos))
			$algo = "sha1";
		ini_set('session.hash_function', $algo);
		debug("Session algo set to " . $algo);
		$path = Core::$instance->config['sessionPath'];
		session_save_path($path); //path to save the session files
		debug("Session path set to " . $path);
		$name = isset(Core::$instance->config['sessionName'])? Core::$instance->config['sessionName'] : "SID";
		session_name($name);
		debug("Session cookie name set to " . $name);
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