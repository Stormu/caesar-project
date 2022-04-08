<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	//Test validation of new profile image url
	if (!preg_match("/^https:\/\/imgur.com\/+[\w\d]+.png$/",$_POST["img_url"]) and (isset($_POST["img_url"]))) {
		$_SESSION['image_valid_url'] = false;
		header("location: edit_profile");
	}	
	//Change profile image URL through API then change session data
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/image?img_url=".urlencode($_POST["img_url"]));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	
	curl_close ($ch);
	
	if ($httpcode == 204) {
		$_SESSION['img_url'] = $_POST["img_url"];
		header("location: edit_profile");
	}
	else {
		$json = json_decode($response, true);
		if($json['detail'] == 'Restricted.') {
			$_SESSION['restricted'] = true;
		}
		else if($json['detail'] == 'Change failed.') {
			$_SESSION['error'] = true;
		}
		header("location: edit_profile");
	}
?>