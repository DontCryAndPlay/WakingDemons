<?php
debug("Loading /test");
if(!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
	header("Location: /");
	exit;
}

$header->assignVar("page", "/test");


Core::$instance->loadLibrary("crest.eve", "Crest");
Core::$instance->loadLibrary("oauth.eve", "Oauth");

//$oauth = new Oauth();
//$oauth->refreshToken();

//echo $_SESSION['oauth']['token'];
$response = "";

$content = new Template("ucp/test");

$content->assignVar("req.method", "GET");
$content->assignVar("req.api", "Character-v4+json");

if(isset($_POST) && count($_POST) > 0) {
	$method = $_POST['method'];
	$route = $_POST['route'];
	$apiversion = $_POST['apiversion'];
	$data = $_POST['data'];
	
	$content->assignVar("req.method", $method);
	$content->assignVar("req.route", $route);
	$content->assignVar("req.api", $apiversion);
	$content->assignVar("req.data", $data);
	
	$crest = new Crest();
	$crest->setUri($route);
	$crest->setMethod($method);
	$crest->setApiVersion($apiversion);
	if($data != "") {
		$data = ($data);
		$crest->setData($data);
	}
	$response = stripslashes(json_encode($crest->doRequest(), JSON_PRETTY_PRINT));
}




$content->assignVar("token", $_SESSION['oauth']['token']);
$content->assignVar("uid", $_SESSION['uid']);
if(isset($response) && $response != "")
	$content->assignVar("response", $response);

$header->render();
$content->Render();
$footer->Render();