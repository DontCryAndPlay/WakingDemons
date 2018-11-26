<?php
if(!function_exists('http_negotiate_language')) {
	//Function from php.net
	function http_negotiate_language ($available_languages,$http_accept_language="auto") {
		$languageprefix = "";
		if ($http_accept_language == "auto" && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		else $http_accept_language = $available_languages[0];
		preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" . 
					   "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i", 
					   $http_accept_language, $hits, PREG_SET_ORDER);
		$bestlang = $available_languages[0];
		$bestqval = 0;
		foreach ($hits as $arr) {
			$langprefix = strtolower ($arr[1]);
			if (!empty($arr[3])) {
				$langrange = strtolower ($arr[3]);
				$language = $langprefix . "-" . $langrange;
			}
			else $language = $langprefix;
			$qvalue = 1.0;
			if (!empty($arr[5])) $qvalue = floatval($arr[5]);
			if (in_array($language,$available_languages) && ($qvalue > $bestqval)) {
				$bestlang = $language;
				$bestqval = $qvalue;
			} else if (in_array($languageprefix,$available_languages) && (($qvalue*0.9) > $bestqval)) {
				$bestlang = $languageprefix;
				$bestqval = $qvalue*0.9;
			}
		}
		return $bestlang;
	}
}
class Language {
	private $loadedDictionaries = array(), $phrases = array();
	private $currentLanguage = 'en';
	private $languages = array();
	public static $instance;
	function __construct() {
		debug("Loaded");
		if(!isset(Core::$instance->config['enabledLanguages'])) Core::$instance->config['enabledLanguages'] = "none";
		debug("Loaded languages: ".Core::$instance->config['enabledLanguages']);
		self::$instance = $this;
		$this->languages = explode(",",Core::$instance->config['enabledLanguages']);
		$this->detectLanguage();
	}
	private function detectLanguage() {
		//Detect user browser's language
		self::$instance->currentLanguage = http_negotiate_language(self::$instance->languages);
		//Defined another language in cookie?
		if(isset($_COOKIE['language']) && is_array(self::$instance->languages))  if(in_array($_COOKIE['language'], self::$instance->languages)) self::$instance->currentLanguage = $_COOKIE["language"];
		//Defined in environment var?
		if(isset($_ENV['language']) && is_array(self::$instance->languages)) if(in_array($_ENV['language'], self::$instance->languages)) self::$instance->currentLanguage = $_ENV['language'];
		//Defined in get var?
		if(isset($_GET['language']) && is_array(self::$instance->languages)) if(in_array($_GET['language'], self::$instance->languages)) self::$instance->currentLanguage = $_GET['language'];
		debug("Language set (".self::$instance->currentLanguage.")");
	}
	public function loadDictionary($dictionary=false) {
		//Undefined dictionary
		if($dictionary === false)
			return false;
		$this->loadDictionaryFromDB($dictionary);
		//Dictionary doesn't exists.
		if(isset($_SESSION['path']))
			$dictionary = $_SESSION['path'] . "/" . $dictionary;
		$f = @fopen("dictionaries/".$dictionary.".php", "r"); 
		if(!$f) {
			error("Can't find dictionary file " . $dictionary);
			return false;
		}
		fclose($f);
		include("dictionaries/".$dictionary.".php");
		array_push($this->loadedDictionaries, $dictionary);
		if (!isset(${$this->currentLanguage})) ${$this->currentLanguage} = array();
		$this->phrases = array_merge($this->phrases, gettype(${$this->currentLanguage}) == "array" ? ${$this->currentLanguage} : array() );
		debug("Loaded dictionary " . $dictionary . " from file");
	}
	public function loadDictionaryFromDB($dictionary = false) {
		if($dictionary === false)
			return false;
		if(in_array($dictionary."_SQL", $this->loadedDictionaries)) {
			error("Already loaded dictionary $dictionary, skipping...");
			return false;
		}
		global $db;
		if(!$db->connected)
			return false;
		$dat = $db->query("SHOW TABLES LIKE 'translations_dictionaries'");
		if(count($dat) == 0) {
			error("translations_dictionaries table not found on database.");
			return false;
		}
		$dat = $db->query("SELECT count(*) as n FROM translations_dictionaries WHERE name = '?' LIMIT 1", $dictionary);
		if(count($dat) == 0 || (isset($dat[0]->n) && $dat[0]->n == 0)) {
			error("Dictionary $dictionary not found on SQL.");
			return false;
		}
		$dat = $db->query("SHOW TABLES LIKE 'translations'");
		if(count($dat) == 0) {
			error("translations table not found on database.");
			return false;
		}
		$dat = $db->query("SELECT key,value FROM translations WHERE dictionary='?' AND language='?'", $dictionary, $this->currentLanguage);
		if(count($dat) == 0) {
			error("Empty dictionary: $dictionary");
			return false;
		}
		array_push($this->loadedDictionaries, $dictionary."_SQL");
		$sqlPhrases = array();
		if(is_array($dat) && count($dat) > 0) {
			foreach($dat as $row)
				$sqlPhrases[$row->key] = $row->value;
		}
		
		$this->phrases = array_merge($this->phrases, $sqlPhrases);
		$sqlPhrases = null;
		debug("Loaded dictionary ".$dictionary . " from SQL");
		return true;
	}
	public function getPhrase($phrase=false, $defaultPhrase=false) {
		if ($phrase === false) return false;
		if (isset($this->phrases[$phrase]))
			return $this->phrases[$phrase];
		else {
			if(!$defaultPhrase)
				return "<span class='missing-phrase' data-dictionaries='".implode($this->loadedDictionaries, "|")."' data-language='".$this->currentLanguage."' style='color: red'>".$phrase."</span>";
			else
				return $defaultPhrase;
		}
	}
}