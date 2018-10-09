<?php
class MySQL {
	private $handler, $query = false, $totaltime = 0, $queries = 0;
	public $connected = false;
	function __construct($data) {
		//new mysqli object
		$this->handler = new mysqli($data['host'], $data['user'], $data['pass'], $data['name']);
		if($this->handler->connect_error) {
			error("Error connecting to MySQL Server (".$this->handler->connect_error.")");
			return false;
		}
		//always use utf-8
		$this->handler->query("SET NAMES utf8");
		debug("Connected to MySQL");
		$this->connected = true;
	}
	private function shutdown() {
		//end of object? end of mysqli
		$this->handler->close();
		debug("MySQLi took ".$this->totaltime." seconds in ".$this->queries." queries.");
	}
	function __destruct() {
		$this->shutdown();
	}
	private function escape($str) {
		//Don't be a bad guy...
		//is real_escape_string 100% secure?
		return $this->handler->real_escape_string($str);
	}
	private function composeQuery($query, $args) {
		//escape all arguments
		foreach($args as $k=>$v)
			$args[$k] = $this->escape($v);
		//replace "?" with their value
		$query = preg_replace_callback('/\?/', function($key) use($args) {
			static $i = 0;
			//en serio, no entiendo esto, pero funciona...
			return isset($args[$i+1]) && $args[$i+1] == ''? $args[$i] : $args[$i++];
		}, $query);
		debug("Query ready: ".$query);
		return $query;
	}
	public function query($query, $args) {
		$time = 0;
		//make the real query
		$query = $this->composeQuery($query, $args);
		//some debug messages
		debug("Executing query...");
		$time -= microtime(true);
		//execute the query
		$queryResult = $this->handler->query($query);
		//more debug...
		if(!$queryResult) {
			error("Error executing query: ".$this->handler->error);
			return false;
		}
		$time += microtime(true);
		$time = round($time, 5);
		$this->totaltime += $time;
		$this->queries++;
		if(gettype($queryResult) == "object" && get_class($queryResult) == "mysqli_result") {
			$tmp = array();
			//save an array of objects in $tmp
			while($obj = $queryResult->fetch_object()) $tmp[] = $obj;
			debug("Query took ".$time." seconds and grabs ".$queryResult->num_rows." rows.");
			//close resultset
			$queryResult->close();
			//if(count($tmp) == 1) $tmp = $tmp[0];
			return $tmp;
		} else {
			debug("Query took ".$time." seconds.");
			return true;
		}
	}
}
?>