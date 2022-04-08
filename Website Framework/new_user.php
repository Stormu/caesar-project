<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	$ignore = False;
	//Test validation of new username
	if ((isset($_POST["username"])) and (!preg_match("/^[a-zA-Z-\d_]{3,15}$/",$_POST["username"]))) {
		$_SESSION['username_invalid'] = True;
		$ignore = True;
		header("location: registration");
	} else if (!isset($_POST["username"])) {
		//If check failed, they do not have a username
		$ignore = True;
		header("location: registration");
	}
	//Test validation of new email
	if ((isset($_POST["email"])) and (!preg_match("/[^@\s]+@[^@\s]+\.[^@\s]/",$_POST["email"]))) {
		$_SESSION['email_invalid'] = True;
		$ignore = True;
		header("location: registration");
	} else if (!isset($_POST["email"])) {
		//If check failed, they do not have an email
		$ignore = True;
		header("location: registration");
	}
	//Test validation of new password
	if ((isset($_POST["password"])) and (!preg_match("/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\!\-\?\$\#])([\!\-\?\$\#\w]+){12,50}$/",$_POST["password"]))) {
		$_SESSION['password_invalid'] = True;
		$ignore = True;
		header("location: registration");
	} else if (!isset($_POST["password"])) {
		//If check failed, they do not have a password
		$ignore = True;
		header("location: registration");
	}		
	
	if ($ignore == False) {
		//Create new user through API then login
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/?username=".urlencode($_POST["username"])."&email=".urlencode($_POST["email"])."&password=".urlencode($_POST["password"]));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		
		curl_close ($ch);
		
		if ($httpcode == 201) {
			session_name('SID');
			ini_set("session.cookie_httponly", True);
			session_start();
			$_SESSION['new_account'] = True;
			$_SESSION['new_username'] = $_POST["username"];
			$_SESSION['new_password'] = $_POST["password"];
			header("location: session");
		}
		else {
			$json = json_decode($response, True);
			if($json['detail'] == 'Email Taken.') {
				$_SESSION['email_taken'] = True;
			} else if($json['detail'] == 'Username Taken.') {
				$_SESSION['username_taken'] = True;
			} else {
				$_SESSION['error_account_creation'] = True;
			}
			header("location: registration");
		}
	}
?>