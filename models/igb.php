<?php
class Igb {
	private $isIGB = false; //In Game Browser
	private $trusted = false;
	private $vars = array();
	private $IGBVars = array('TRUSTED','SERVERIP','CHARNAME','CHARID','CORPNAME','CORPID','ALLIANCENAME','ALLIANCEID','REGIONNAME','CONSTELLATIONNAME','SOLARSYSTEMNAME','STATIONNAME','STATIONID','CORPROLE','SOLARSYSTEMID','WARFACTIONID','SHIPID','SHIPNAME','SHIPTYPEID','SHIPTYPENAME');
	function __construct() {
		if(strtolower($_SERVER['HTTP_EVE_TRUSTED']) == "yes") {
			$this->isIGB = true;
			$this->trusted = true;
		} else if (strtolower($_SERVER['HTTP_EVE_TRUSTED']) == "no") {
			$this->isIGB = true;
			$this->trusted = false;
		} else $this->isIGB = false;
	}
	function loadVariables() {
		if ( ! $this->isIGB || ! $this->trusted ) return false;
		foreach($this->IGBVars as $k=>$v) 
			$this->vars[$v] = $_SERVER['HTTP_EVE_' . $v];
		return true;
	}

	function getVariables() {
		return $this->vars;
	}

}