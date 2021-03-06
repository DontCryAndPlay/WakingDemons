<?php
// RENAME THIS FILE TO configuration.php
$production = false;
$debug = true;
$underConstruction = false;
$allowedIPs = array('');
$ignore = array('');
$enabledLanguages = array("es");

$session['sessionLifeTime'] = 108000; //1 month
$session['sessionPath'] = "/tmp/sessions";

/*type of db - ie:
	mysqli
	pgsql
	mssql
	oracle
	...
Currently supporting MySQLi
*/
$db['dbhandler'] = "mysqli";
$db['dbuser'] = "dbuser";
$db['dbpass'] = "dbpassword";
$db['dbname'] = "dbname";
$db['dbhost'] = "127.0.0.1";

$config['oauth']['clientID'] = "OAUTH_CLIENTID";
$config['oauth']['secretKey'] = "OAUTH_SECRETKEY";
$config['oauth']['callback'] = "https://wakingdemons.com/login";
?>
