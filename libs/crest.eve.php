<?php
class Crest {
	private $uri = null;
	private $method = "GET";
	private $tokenType, $token;
	private $crestServer = "https://esi.tech.ccp.is/latest";
	private $data = null;
	private $requests = 0, $time=0;

	function __destruct() {
		debug("Crest took " . $this->time . " seconds in " . $this->requests . " requests.");
	}

	function __construct() {
		debug("Crest loaded");
		$this->tokenType = $_SESSION['oauth']['tokenType'];
		$this->token = $_SESSION['oauth']['token'];
	}
	public function setToken($type = false, $token = false) {
		if(!$type || !$token) return false;
		//TODO: validate it
		$this->tokenType = $type;
		$this->token = $token;
		return true;
	}
	public function setData($data = false) {
		if(!$data) return false;
		$this->data = $data;
		return true;
	}
	public function setUri($uri) {
		if($uri == "") return false;
		$this->uri = $uri;
		return true;
	}
	public function setApiVersion($apiVersion=false) {
		error("This call is deprecated and will be removed.");
		return true;
	}
	public function setMethod($method = "GET") {
		if($method == "GET") return true;
		$this->method = $method;
		return true;
	}
	public function doRequest() {
		debug("Requesting " . $this->uri . " to crest...");
		$time = 0;
		$time -= microtime(true);
		$curlOptions = array(
			CURLOPT_CUSTOMREQUEST  => $this->method,
			CURLOPT_URL			   => $this->crestServer . $this->uri,
			CURLOPT_HTTPHEADER	   => array(
										"Authorization: " . $this->tokenType . " " . $this->token,
										"Accept: application/json",
										"Content-Type: application/json",
									  ),
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
		if($this->method == "POST" || $this->method == "PUT") {
			$curlOptions[CURLOPT_POST] = true;
			$curlOptions[CURLOPT_POSTFIELDS] = $this->data;
		}
		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$data = json_decode( curl_exec($ch), true );
		curl_close($ch);
		$time += microtime(true);
		$time = round($time, 5);
		$this->time += $time;
		$this->requests++;
		return $data;
	}
}
?>