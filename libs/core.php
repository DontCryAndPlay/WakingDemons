<?php
class DummyClass {
	protected $className = "dummy";
	public function __call($method, $args) {
		debug("$this->className not loaded (see configuration file): $method ignored");
	}
}
class Core {
	private $dbSupport = array("mysql","mysqli");
	public $production;
	public $debug = false, $htmlfriendly = true;
	public static $instance;
	public $config = array(), $ignore = array();
	public $buffer = false;
	private $path;
	private $loadedLibraries = array();
	function __construct() {
		umask(0077);
		require("configuration.php");
		self::$instance = $this;
		if ( isset($underConstruction) && $underConstruction ) {
			if ( isset($allowedIPs) && is_array($allowedIPs) ) {
				if ( ! in_array($_SERVER['REMOTE_ADDR'], $allowedIPs ) ) {
					define("UNDERCONSTRUCTION", true);
				}
			}
		}

		if ( defined("UNDERCONSTRUCTION") && UNDERCONSTRUCTION )
			$this->debug = false;
		else
			$this->debug = isset($debug)? $debug : false;

		define("DEBUG", $this->debug);
		$this->ignore = isset($ignore)? $ignore : array();
		$this->loadStdLibraries();
		$this->loadSettings();
		debug("Loaded");
		if(!PRODUCTION) {
			ini_set('display_errors',true);
			ini_set('display_startup_errors',true);
			error_reporting(E_ALL);
			ini_set("log_errors", true);
		}
		register_shutdown_function(array(Core::$instance, 'shutdown'));
	}
	public function shutdown() {
		debug("Shutting down...");
		global $db;
		$db = null;
		gc_collect_cycles();
		unset($db);
		gc_collect_cycles();
		Core::$instance->buffer = false;
	}
	private function loadSettings() {
		require("configuration.php");
		define("PRODUCTION", $production);
		$this->production = $production;
		$this->config = array_merge($this->config, $db)??array();
		$this->config = array_merge($this->config, $session)??array();
		if(isset($config) && is_array($config))
			$this->config = array_merge($this->config, $config);

		set_include_path(get_include_path() . PATH_SEPARATOR . "./libs" . PATH_SEPARATOR . "./libs/thirdparty");

		if(isset($enabledLanguages)) $this->config['enabledLanguages'] = $enabledLanguages;

		global $db;
		$db = new DB($this->config['dbhandler']);
		$dat = $db->query("SHOW TABLES LIKE 'config'");
		if(count($dat) == 0) {
			$db->query("CREATE TABLE `config` (
				`key` VARCHAR(255) NOT NULL,
				`value` VARCHAR(255) NULL DEFAULT NULL,
				PRIMARY KEY (`key`)
				)
			ENGINE=InnoDB;");
		}
		$dat = $db->query("SELECT `key`,value FROM config");
		if ($dat) {
			foreach($dat as $k=>$v)
				$this->config[$v->key] = $v->value;
		}
		if(!isset($this->config['sessionPath']) || $this->config['sessionPath'] == '')
			$this->config['sessionPath'] = session_save_path();
		debug("Configuration set");
	}
	public function loadLibrary($lib = false, $class = false) {
		if(!$lib || !$class) {
			debug("Empty lib or class");
			return false;
		}
		
		if(in_array($lib, $this->loadedLibraries))
			return true;

		if(in_array($lib, $this->ignore)) {
			debug("Ignoring lib ".$lib);
			$this->createDummyClass($class);
			return true;
		}
		array_push($this->loadedLibraries, $lib);
		debug("Loading ".$lib);
		include("libs/".$lib.".php");
	}
	private function loadStdLibraries() {
		$libs = array(	"language"=>"Language",
						"template"=>"Template",
						"db"=>"DB",
						"model"=>"Model",
						"session"=>"Session"
					);
		debug("Loading standard libraries...");
		foreach ($libs as $lib=>$class)
			$this->loadLibrary($lib, $class);

		debug("OK");
	}
	private function createDummyClass($className) {
		eval("class $className extends DummyClass {protected\$className=\"$className\";};
			");
	}
	public function getDbSupport() {
		return $this->dbSupport;
	}
	public function salt() {
		//Random salt for each user... hehehe...
		return str_replace("+",".", substr(base64_encode(openssl_random_pseudo_bytes(17)),0,22));
	}
	public function invalidatePath() {
		if(!isset($_SESSION['path']) || $_SESSION['path'] == "") return false;
		$this->path = $_SESSION['path'];
		unset($_SESSION['path']);
	}
	public function revalidatePath() {
		$_SESSION['path'] = $this->path;
		unset($this->path);
	}
}
function debug($message) {
	if(DEBUG === true) {
		$buff = Core::$instance->buffer;
		if($buff) ob_start();
		list(,$caller)=debug_backtrace(false);
		if(isset($caller['class'])){
			if ($caller['class'] == "DummyClass"){
				list(,,$caller) = debug_backtrace(false);
			}
			print "[".$caller['class']."][".$caller['function']."]: ".$message.((Core::$instance->htmlfriendly)? "<br>\n" : "\n");
		} else
			print "[".$caller['function']."]: ".$message.((Core::$instance->htmlfriendly)? "<br>\n" : "\n");
		if($buff) {
			global $buffer;
			$buffer .= ob_get_clean();			
		}
	}
}
function error($message) {
	if(!PRODUCTION) {
		$buff = Core::$instance->buffer;
		if($buff) ob_start();
		print "<strong>Error: </strong> ".$message.((Core::$instance->htmlfriendly)? "<br>\n" : "\n");
		if($buff) {
			global $buffer;
			$buffer .= ob_get_clean();
		}
	} else {
		//TODO: Write to file
	}
}
?>