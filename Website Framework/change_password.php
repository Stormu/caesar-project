<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	//Test validation of new password
	if (!preg_match("/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\!\-\?\$\#])([\!\-\?\$\#\w]+){12,50}$/",$_POST["new_password"]) and (isset($_POST["new_password"]))) {
		$_SESSION['password_validation_fail'] = true;
		header("location: edit_profile");
	}	
	//Change new username through API then change session data
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/password?current_password=".urlencode($_POST["current_password"])."&new_password=".urlencode($_POST["new_password"]));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	
	curl_close ($ch);
	
	if ($httpcode == 204) {
		$_SESSION['password_updated'] = true;
		header("location: edit_profile");
	}
	else {
		$json = json_decode($response, true);
		if($json['detail'] == 'Change Failed.') {
			$_SESSION['error_password'] = true;
		}
		else if($json['detail'] == 'Wrong Password.') {
			$_SESSION['incorrect_credentials'] = true;
		}
		header("location: edit_profile");
	}
?>