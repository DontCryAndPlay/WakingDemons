<?php

$output = array();

$locator = new Model("crest/Location");
$ok = $locator->process();
$newShip = $locator->hasNewShip();
$newLocation = $locator->hasNewLocation();

if($newShip) {
	$output['ship_type_name'] = $_SESSION['crest']['location']['ship']['ship_type_name'];
	$output['ship_name'] = $_SESSION['crest']['location']['ship']['ship_name'];
}
if($newLocation) {
	$output['solar_system_name'] = $_SESSION['crest']['location']['solar_system_name'];
	$output['structure_name'] = $_SESSION['crest']['location']['structure_name'];
}


$mailer = new Model("crest/Mail");
$ok = $mailer->process();
$newMail = $mailer->hasNewMails();

if($newMail)
	$output['new_mail'] = true;


echo json_encode($output);