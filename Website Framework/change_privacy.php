<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	//Test validation of privacy number
	if (($_POST["privacy"] == 0 or $_POST["privacy"] == 1) and isset($_POST["privacy"])) {
		//Change new username through API then change session data
		$ch = curl_init();
		$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/privacy?privacy=".urlencode($_POST["privacy"]));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	
		curl_close ($ch);
	
		if ($httpcode == 204) {
			$_SESSION['privacy_change'] = true;
			$_SESSION['privacy'] = $_POST["privacy"];
			header("location: edit_profile");
		}
		else {
			if($json['detail'] == 'Change failed.') {
				$_SESSION['privacy_change_error'] = true;
			}
			header("location: edit_profile");
		}
	}
	else{
		header("location: edit_profile");
	}
?>