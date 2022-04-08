<?php
//Get new access token if refresh token exists and is valid
function grabUsername($UID) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/basic?UID=".$UID);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	
	curl_close ($ch);
	
	$json = json_decode($response, true);
	
	if (isset($json['username'])) {
		return $json['username'];
	}
	else {
		return 'Deleted User';
	}
} 