<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	//Change new username through API then change session data
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/image");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	
	curl_close ($ch);
	
	if ($httpcode == 204) {
		unset($_SESSION['img_url']);
		header("location: edit_profile");
	}
	else {
		if($json['detail'] == 'Change failed.') {
			$_SESSION['error'] = true;
		}
		header("location: edit_profile");
	}
?>