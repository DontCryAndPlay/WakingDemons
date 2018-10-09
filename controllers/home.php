<?php
if(isset($_SESSION['auth']) && $_SESSION['auth'] == true) {
	header("Location: /ucp");
	exit;
}
debug("home is up");
$header->assignVar("page", "home");
$header->render();
$language->loadDictionary("home");

$content = "";

$content = new Template("home");

if(isset($_GET['accessDenied']))
	$content->assignVar("message", '<h1><font color="#F00">Aceso denegado</font></h1>');
else
	$content->assignVar("message", '<h1>Inicio de sesión requerido</h1>');


$content->Render();
$footer->Render();
?>