<?php
$header->assignVar("page", "/mail");

$language->loadDictionary("ucp/mail");

$content = new Template("ucp/mail");

$maildata = "";

@$label = (string)explode($_GET['do']."/", $_SERVER['REQUEST_URI'])[1];

switch ($label) {
	case 'Alliance':
		$label = "[Alliance]";
		break;
	case 'Corp':
		$label = "[Corp]";
		break;
	case 'All':
		$label = "";
		break;
	case '':
		$label = "Inbox";
		break;
}

foreach($_SESSION['crest']['mail'] as $k=>$v) {
	if(!is_array($v)) break;
	$labels = array();
	if(is_array($v['labels']))
		foreach($v['labels'] as $label_id)
			$labels[] = $_SESSION['crest']['mail_labels'][$label_id]['name'];
	
	$status = $v['is_read']? "Read" : "Unread";

	if(isset($label) && $label != "")
		if(!in_array($label, $labels)) continue;

	$labels = implode(", ", $labels);

	$maildata .= "<tr class=\"mouseOverEffect\"><td>" . $status . "</td><td>" . $v['from'] . "</td><td>" . $v['subject'] . "</td><td>" . $v['timestamp'] . "</td><td>" . $labels . "</td></tr>";
}


$content->assignVar("mails", $maildata);

$header->Render();
$content->Render();
$footer->Render();
?>