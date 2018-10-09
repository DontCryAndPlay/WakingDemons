<?php

class SEAT {
	private $token;
	private $host;
	private $connected=false;
	function __construct() {
		$this->token = Core::$instance->config['SEAT']['token'];
		$this->host = Core::$instance->config['SEAT']['host'];
	}
	public function fetch($endpoint) {
		$data = "";
		$this->executeTransaction($endpoint, $data);
		return $data;
	}
	private function executeTransaction($endpoint, &$output) {
		$curlOptions = array(
			CURLOPT_URL			   => "https://" . $this->host . "/" . $endpoint,
			CURLOPT_HTTPHEADER	   => array("X-Token: " . $this->token, "Accept:application/json"),
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
		$output=$data;
	}
}
