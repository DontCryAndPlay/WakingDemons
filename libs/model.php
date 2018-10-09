<?php
class Model {
	private $handler = false;
	function __construct($model = false) {
		if(!$model) {
			error("No model specified.");
			return false;
		}
		$file = strtolower($model);
		if(strstr($model, "/")) {
			$model = explode("/",$model);
			$model = $model[count($model)-1];
		}
		if(isset($_SESSION['path']))
			$file = $_SESSION['path'] . "/" . $file;
		$f = @fopen("models/".$file.".php", "r");
		if(!$f) {
			error("Model ".$file." doesn't exists.");
			return false;
		}
		fclose($f);


		debug("Loading model: ".$file);
		require_once("models/".$file.".php");
		$this->handler = new $model;
	}
	function __call($function, $arguments=array()) {
		if($this->handler) return call_user_func_array(array((method_exists($this, $function)? $this : $this->handler),$function),$arguments);
		else return false;
	}
}
?>