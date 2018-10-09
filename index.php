<?php
header("X-Bypass-Cache: true");
ob_start();
require("libs/core.php");

$core=new Core();

$pages = array();
$pages['home'] = true;
$pages['register'] = true;
$pages['login'] = true;
$pages['ucp'] = true;
$pages['logout'] = true;

$alias = array();
$alias['404'] = "home";
$alias['test'] = "ucp"; 
$alias['logout'] = "ucp"; 
$alias['results'] = "ucp";
$alias['newPost'] = "ucp"; 

$language = new Language;

$path = explode("?", $_SERVER['REQUEST_URI']);

$path=explode("/", $path[0]);
$page = $path[1];

if(!isset($page) || $page == "") $page = "home";
else $page = strtolower($page);

if (!isset($pages[$page]) || $pages[$page] == "") {
	if(!isset($alias[$page]) || $alias[$page] == "") {
		$page = "404";
		if($alias[$page] != "") $page = $alias[$page];
	} else $page = $alias[$page];
}

if (isset($_COOKIE['SID'])) { //Session active
	$session = new Session();
	if(isset($_SESSION['auth']) && $_SESSION['auth'] === true && ($page == "home" || $page == ""))
		$page = "ucp";
}
if($page == "ucp" && (!isset($_SESSION['auth']) || $_SESSION['auth'] == 0)) $page = "home";


//capture buffer (debug, warnings...) to avoid breaking html
$buffer = ob_get_clean();
$core->buffer = true;
//start showing content...

$header = new Template("header");
//$language->loadDictionary("header");
$footer = new Template("footer");
include("controllers/".$page.".php");
//Show all buffer contents after contents is displayed
//echo $buffer;

?>