<?php

class Oauth {

	private $code, $state;
	private $data = array();
	private $clientID = "";
	private $secretKey = "";
	private $callback = "";
	private $token = "";
	private $tokenType = "";
	private $refreshToken = "";
	private $scope = "";
	function __construct() {
		new Session();
		if ( isset($_SESSION['oauth']['state']) ) 
			$this->state = $_SESSION['oauth']['state'];
		if ( isset($_SESSION['oauth']['code']) )
			$this->code = $_SESSION['oauth']['code'];
		if ( isset($_SESSION['oauth']['token']) ) {
			$this->token = $_SESSION['oauth']['token'];
			$this->tokenType = $_SESSION['oauth']['tokenType'];
		}
		if ( isset($_SESSION['oauth']['refreshToken']) ) 
			$this->refreshToken = $_SESSION['oauth']['refreshToken'];
		
		$this->clientID = Core::$instance->config['oauth']['clientID'];
		$this->secretKey = Core::$instance->config['oauth']['secretKey'];
		$this->callback = Core::$instance->config['oauth']['callback'];
	}
	public function setScopes($data = array()) {
		if(count($data) == 0 ) return false;
		$this->scope = implode("%20", $data);

	}
	private function restartAuth($message="") {
		//TODO: maybe log?
		//echo $message;
		$_SESSION['auth'] = false;
		$_SESSION['oauth'] = false;
		unset($_SESSION['oauth']);
		header("Location: /login");
		exit;
	}
	function createAuth() {
		$_SESSION['oauth']['state'] = sha1(uniqid(rand(), true));
		$_SESSION['oauth']['stage'] = 1;
		header("Location: https://login.eveonline.com/oauth/authorize/?response_type=code&redirect_uri=" . $this->callback . "&client_id=" . $this->clientID . "&scope=". $this->scope ."&state=" . $_SESSION['oauth']['state']);
		exit;
	}
	function verifyAuth($code, $state) {
		$_SESSION['oauth']['code'] = $code;
		$this->code = $code;
		if( $this->state != $state || $_SESSION['oauth']['stage'] != 1)
			$this->restartAuth("Stage not 1 or missed state");
		
		if ( $this->validateAuth() ) {
			$_SESSION['oauth']['refreshToken'] = $this->data['refresh_token'];
			$_SESSION['oauth']['cooldown'] = time()+$this->data['expires_in'];
			$_SESSION['oauth']['stage'] = 2;
			$_SESSION['oauth']['token'] = $this->data['access_token'];
			$this->token = $this->data['access_token'];
			$_SESSION['oauth']['tokenType'] = $this->data['token_type'];
			$this->tokenType = $this->data['token_type'];
			//TODO: save token to db?
			$this->fetchUserData();
			return true;
		} else 
			$this->restartAuth("validateAuth failure");
		
	}
	private function validateAuth() {
		if ( $this->code == "" )
			$this->restartAuth("code is missing");
		$curlOptions = array(
			CURLOPT_URL			   => "https://login.eveonline.com/oauth/token",
			CURLOPT_HTTPHEADER	   => array("Content-Type: application/x-www-form-urlencoded"),
			CURLOPT_USERPWD		   => $this->clientID . ":" . $this->secretKey,
			CURLOPT_POST		   => true,
			CURLOPT_POSTFIELDS	   => "grant_type=authorization_code&code=".$this->code,
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "wakingdemons.com", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => true,
		);
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$data = json_decode( curl_exec($ch), true );
		curl_close($ch);
		if ( isset($data['error']) )
			return false;
		
		$this->data = $data;
		return true;
	}
	
	private function localAuthorization($uid, $name) {
		$login = new Model("Login");
		return $login->checkAccess($uid, $name);
	}

	private function fetchUserData() {
		if( $this->token == "" || $_SESSION['oauth']['stage'] != 2)
			$this->restartAuth("token missing or stage not 2");
		$curlOptions = array(
			CURLOPT_URL			   => "https://login.eveonline.com/oauth/verify",
			CURLOPT_HTTPHEADER	   => array("Authorization: " . $this->tokenType . " " . $this->token),
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "wakingdemons.com", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => true,
		);
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$data = json_decode( curl_exec($ch), true );
		curl_close($ch);

		if( isset($data['CharacterID']) ) {
			if ( ! $this->localAuthorization($data['CharacterID'], $data['CharacterName']) ) {
				unset($_SESSION['oauth']);
				header("Location: /?accessDenied");
				exit;
			}
			
			$_SESSION['CharacterOwnerHash'] = $data['CharacterOwnerHash'];
			$_SESSION['auth'] = true;
		} else
			$this->restartAuth("remote authorization failure");
	}
	function refreshToken() {
		if ( $this->refreshToken == "" )
			$this->restartAuth("RefreshToken is missing");
		$curlOptions = array(
			CURLOPT_URL			   => "https://login.eveonline.com/oauth/token",
			CURLOPT_HTTPHEADER	   => array("Content-Type: application/x-www-form-urlencoded"),
			CURLOPT_USERPWD		   => $this->clientID . ":" . $this->secretKey,
			CURLOPT_POST		   => true,
			CURLOPT_POSTFIELDS	   => "grant_type=refresh_token&refresh_token=".$this->refreshToken,
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "wakingdemons.com", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => true,
		);
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$data = json_decode( curl_exec($ch), true );
		curl_close($ch);
		if ( isset($data['error']) )
			return false;
		
		$_SESSION['oauth']['token'] = $data['access_token'];
		$_SESSION['oauth']['cooldown'] = time() + $data['expires_in'];
		$this->token = $data['access_token'];
		return true;
	}
}

?>
