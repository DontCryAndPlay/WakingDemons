<?php
class Template {
	private $rawData, $processedData, $variables=array();
	function __construct($template) {
		if(isset($_SESSION['path']))
			$template = $_SESSION['path'] . "/" . $template;
		$f = @fopen("templates/".$template.".html", "r");
		if(!$f) {
			error("Can't open template file ".$template);
			return false;
		}
		$data = '';
		while(!feof($f)) 
			$data .= fread($f, 8192);
		fclose($f);
		$this->rawData=$data;
		debug("Loaded ".$template);
		//return $data;	
	}
	public function assignVar($var, $value) {
		if(strstr($var, ".")) {
			$data = explode(".", $var);
			$prev = &$this->variables;
			for($i=0;$i<count($data);$i++) {
				if($i == count($data)-1) 
					$prev[$data[$i]] = $value;
				else {
					if(!isset($prev[$data[$i]]))
						$prev[$data[$i]] = array();
					$prev = &$prev[$data[$i]];
				}
			}
			return true;
		} else $this->variables[$var] = $value;
	}
	public function assignVars($data) {
		if(!is_array($data)) return false;
		foreach($data as $k=>$v)
			$this->assignVar($k, $v);
	}
	public function render($return=false) {
		//render translations
		$this->processedData = preg_replace_callback('/<%([^%]*)%>/', function($key) {
			return Language::$instance->getPhrase($key[1]);
		}, $this->rawData);
		//render variables
		global $buffer;
		$this->assignVar("buffer", $buffer);
		$vars = $this->variables;
		$this->processedData = preg_replace_callback('/\$([\w\.]*)\$/', function($key) use ($vars) {
			if(strstr($key[1], ".")) { //Php is...
				$data = explode(".", $key[1]); // ...consistent
				$var = $vars;
				foreach($data as $v) {
					if (isset($var[$v])) {
						$var = is_array($var)? $var[$v] : $vars[$v];
					}
				}
				if($var == $vars || is_array($var)) $var="";
				return $var;
			} else { 
				return isset($vars[$key[1]]) && !is_array($vars[$key[1]]) ? $vars[$key[1]] : "";
			}
		}, $this->processedData);
		$this->processedData = str_replace("\%", "%", $this->processedData);
		if(!$return) echo $this->processedData;
		else return $this->processedData;
		unset($this->processedData);
	}
	public function fetch() {
		return $this->render(true);
	}
}
?>