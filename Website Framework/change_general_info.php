<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	$alreadyFirst = false;
	$request = 'general?';
	//Test validation of new email
	if ($_POST["email"] != "") {
		if (!preg_match("/[^@\s]+@[^@\s]+\.[^@\s]/",$_POST["email"])) {
			$_SESSION['email_invalid'] = true;
			header("location: edit_profile");
		} else {
			$request .= "email=".urlencode($_POST["email"]);
			$alreadyFirst = true;
		}
	}
	//Test validation of new first name
	if ($_POST["first_name"] != "") {
		if (!preg_match("/^[a-zA-Z\s]{0,20}$/",$_POST["first_name"])) {
			$_SESSION['first_name_invalid'] = true;
			header("location: edit_profile");
		} else {
			if ($alreadyFirst == true) {
				$request .= "&first_name=".urlencode($_POST["first_name"]);
			}else {
				$request .= "first_name=".urlencode($_POST["first_name"]);
				$alreadyFirst = true;
			}
		}
	}
	//Test validation of new last name
	if ($_POST["last_name"] != "") {
		if (!preg_match("/^[a-zA-Z\s]{0,20}$/",$_POST["last_name"])) {
			$_SESSION['last_name_invalid'] = true;
			header("location: edit_profile");
		} else {
			if ($alreadyFirst == true) {
				$request .= "&last_name=".urlencode($_POST["last_name"]);
			}else {
				$request .= "last_name=".urlencode($_POST["last_name"]);
				$alreadyFirst = true;
			}
		}
	}
	//Test validation of new mobile number
	if ($_POST["mobile_number"] != "") {
		if (!preg_match("/^[\d\w]{0,15}$/",$_POST["mobile_number"])) {
		$_SESSION['mobile_number_invalid'] = true;
		header("location: edit_profile");
		} else {
			if ($alreadyFirst == true) {
				$request .= "&mobile_number=".urlencode($_POST["mobile_number"]);
			} else {
				$request .= "mobile_number=".urlencode($_POST["mobile_number"]);
				$alreadyFirst = true;
			}
		}
	}
	//Change new username through API then change session data
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/".$request);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	
	curl_close ($ch);
	
	if ($httpcode == 204) {
		header("location: edit_profile");
	}
	else {
		$json = json_decode($response, true);
		if($json['detail'] == 'Email Taken.') {
			$_SESSION['email_taken'] = true;
		}
		header("location: edit_profile");
	}
?>