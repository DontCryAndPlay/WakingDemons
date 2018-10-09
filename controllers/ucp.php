<?php
if(!isset($_SESSION['auth']) || $_SESSION['auth'] == false) {
	header("Location: /");
	exit;
}

//User Control Panel main router.

$ucpPages = array();
$ucpPages['test'] = true;
$ucpPages['mail'] = true;
$ucpPages['pinger'] = true;


$page = isset($path[1]) ? $path[1] : "";

if($page == '' || !isset($ucpPages[$page]) || !$ucpPages[$page]) $page = "home";

Core::$instance->loadLibrary("seat.eve", "SEAT");
Core::$instance->loadLibrary("crest.eve", "Crest");
Core::$instance->loadLibrary("oauth.eve", "Oauth");

if(time() >= $_SESSION['oauth']['cooldown'] - 5) {
	$oauth = new Oauth();
	$oauth->refreshToken();
}

$crest = new Crest();

//$headerData = new Model("ucp/Header");
$header = new Template("ucp/header");
//$header->assignVars($headerData->fetchVars());
//$language->loadDictionary("ucp/header");

$footer = new Template("ucp/footer");

if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'])
	$header->assignVar("TEST", '<li><a href="/test">Test</a></li>');

$userData = new Model("ucp/User");
$userData->fetchSeat("CharacterInfo");
$header->assignVar("background", $userData->fetchBackground());
$header->assignVars($_SESSION);


include("controllers/ucp/".$page.".php");
?>