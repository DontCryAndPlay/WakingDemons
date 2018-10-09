<?php
$language->loadDictionary("register");
if(isset($_POST) && count($_POST) > 0) {
	$response = array();
	$empty = false;
	foreach($_POST as $k=>$v) {
		${$k} = trim($v);
		if(${$k} == '') $empty = true;
	}
	if($empty) {
		$response['status'] = "ko";
		$response['message'] = $language->getPhrase("emptyfields");
		print json_encode($response);
		exit;
	}
	if($registerPassword != $registerPasswordRepeation) {
		$response['status'] = "ko";
		$response['message'] = $language->getPhrase("passwordMatchError");
		print json_encode($response);
		exit;
	}
	$register = new Model("Register");
	//email exists?
	if($register->emailExists($registerEmail)) {
		$response['status'] = "ko";
		$response['message'] = $language->getPhrase("emailExists");
		print json_encode($response);
		exit;
	}
	//gender specific code...
	$registerGender = isset($registerGender)? 'male' : 'female';
	$data = array();
	$data['password'] = $registerPassword;
	$data['email'] = $registerEmail;
	$data['firstname'] = $registerFirstname;
	$data['lastname'] = $registerLastname;
	$data['gender'] = $registerGender;
	if ($register->newUser($data)) {

                $login = new Model("Login");
	        if ($login->login($data['email'], $data['password'])) {
		    $session = new Session();
		    $session->createUserSession();
		    $login->setSessionData();
                }

		$response['status'] = "ok";
		$response['message'] = $language->getPhrase("registerOk");
	} else {
		$response['status'] = "ko";
		$response['message'] = $language->getPhrase("unexpectedError");
	}
	print json_encode($response);
	exit;
}
?>