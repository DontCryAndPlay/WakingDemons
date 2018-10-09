<?php
$header->render();
$language->loadDictionary("ucp/logout");

$contentData = new Model("ucp/logout");
$content = new Template("ucp/logout");
$content->Render();

$footer->Render();
?>