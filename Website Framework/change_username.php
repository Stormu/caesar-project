<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	$ignore = False;
	if(!isset($_POST["username"])) {
		header("location: edit_profile");
		$ignore = True;
	}
	//Test validation of new username
	if ((isset($_POST["username"])) and (!preg_match("/^[a-zA-Z-\d_]{3,15}$/",$_POST["username"]))) {
		$_SESSION['username_valid_fail'] = true;
		$ignore = True;
		header("location: edit_profile");
	}
	
	if($ignore == False) {
		//Change new username through API then change session data
		$ch = curl_init();
		$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/username?username=".urlencode($_POST["username"]));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		
		curl_close ($ch);
		
		if ($httpcode == 204) {
			$_SESSION['username'] = $_POST["username"];
		}
		else {
			$json = json_decode($response, true);
			if($json['detail'] == 'Username Taken.') {
				$_SESSION['username_taken'] = true;
			}
			else if($json['detail'] == 'Restricted Change.') {
				$_SESSION['username_restricted'] = true;
			}
		}
	}
	
	header("location: edit_profile");
?>