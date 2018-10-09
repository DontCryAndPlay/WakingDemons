<?php
$header->assignVar("page", "/ucp");

$language->loadDictionary("ucp/home");

$content = new Template("ucp/home");

//var_dump($_SESSION);

/*$oauth = new Oauth();
$oauth->refreshToken();*/

/*$crest = new Crest();
$crest->setUri("/characters/" . $_SESSION['EVE']['CHAR']['ID'] . "/location/");
$response = $crest->doRequest();*/

//var_dump($_SESSION);


$header->Render();
$content->Render();
$footer->Render();
?>