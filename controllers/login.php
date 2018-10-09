<?php

/*********
Stages:
 0 - new session
 1 - obtaining auth code
 2 - verified auth code
 3 - authenticated with database
*********/
//require("libs/oauth.eve.php"); //DEPRECATED


Core::$instance->loadLibrary("oauth.eve", "Oauth");

$scope = array(
			"publicData","remoteClientUI","fleetWrite","fleetRead",
			"corporationWalletRead", "corporationStructuresRead", "corporationMembersRead",
			"corporationMarketOrdersRead", "corporationKillsRead", "corporationIndustryJobsRead",
			"corporationContractsRead","characterWalletRead","characterSkillsRead",
			"characterNotificationsRead","characterNavigationWrite","characterMarketOrdersRead",
			"characterMailRead","characterLoyaltyPointsRead","characterLocationRead",
			"characterKillsRead","characterIndustryJobsRead","characterFittingsWrite",
			"characterFittingsRead","characterContractsRead","characterContactsWrite",
			"characterContactsRead","characterClonesRead","characterCalendarRead",
			"characterAccountRead", "characterAssetsRead","characterBookmarksRead","characterChatChannelsRead","esi-location.read_location.v1","esi-location.read_ship_type.v1", "esi-universe.read_structures.v1","esi-mail.read_mail.v1","esi-mail.organize_mail.v1","esi-mail.send_mail.v1"
		);

$oauth = new Oauth();
$oauth->setScopes($scope);



if(isset($_GET['code']) && isset($_GET['state'])) { //stage 1 & 2

	if($oauth->verifyAuth($_GET['code'], $_GET['state']) && $_SESSION['auth']) {
		header("Location: /login"); // remove query string data
		exit;
	}

} else { //stage 0 & validated

	if( isset($_SESSION['auth']) && $_SESSION['auth'] === true ) {
		header("Location: /"); //successful login 
		exit;
	}
	else
		$oauth->createAuth();
}


?>