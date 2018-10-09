<?php
class DB {
	public $connected = false;
	private $dbHandler, $type;
	function __construct($type = false) {
		if(!in_array($type, Core::$instance->getDbSupport())) {
			error("Unsupported DB type ".$type);
			return false;
		}
		$data['host'] = Core::$instance->config['dbhost'];
		$data['user'] = Core::$instance->config['dbuser'];
		$data['pass'] = Core::$instance->config['dbpass'];
		$data['name'] = Core::$instance->config['dbname'];
		$this->type = $type;
		debug("Loading ".$type);
		foreach ($data as $k=>$v) {
			if($v == ""){
				debug("DB Configuration not set, ignoring DB...");
				return false;
			}
		}
		switch ($type) {
			case 'mysql':
			case 'mysqli':
				include("mysql.php");
				$this->dbHandler = new MySQL($data);
				break;
		}
		$this->connected = $this->dbHandler->connected;
	}
	public function close() {
		unset($this->dbHandler);
		return true;
	}
	public function query($query=false) {
		if(!$this->connected) {
			debug("Query error: Not connected to db");
			return false;
		}
		if($query === false) {
			error("NULL query received, expected string");
			return false;
		}
		//detect caller
		list(,$caller)=debug_backtrace(false);
		debug("Query from ".$caller['class']."->".$caller['function']."() : ".$query);
		$args = func_get_args();
		array_shift($args);
		$result = $this->dbHandler->query($query, $args);
		if ($result === false) {
			error("Query from ".$caller['class']."->".$caller['function']."()  failed");
			return false;
		} else return $result;
	}
	function __destruct() {
		unset($this->dbHandler);
	}
}
?>