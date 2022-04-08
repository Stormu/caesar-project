<?php
//Get new access token if refresh token exists and is valid
function regenerateAccessToken($refresh_token) {
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$refresh_token;
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/token/refresh");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	
	curl_close ($ch);
	
	$json = json_decode($response, true);
	
	if (isset($json['access_token'])) {
		setcookie("APP_AT", $json['access_token'], time()+60*60, "/", httponly:true );
		$authorization = "Authorization: Bearer ".$json['access_token'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/current");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$json = json_decode($response, true);
		
		if (isset($json['username'])) {
			session_name('SID');
			ini_set("session.cookie_httponly", True);
			session_start();
			$_SESSION['loggedin'] = true;
			$_SESSION['UID'] = $json['UID'];
			$_SESSION['username'] = $json['username'];
			$_SESSION['img_url'] = $json['img_url'];
			$_SESSION['scope'] = $json['scope'];
			$_SESSION['privacy'] = $json['public'];
		} 
	}
}

function auto_logout() {
	session_name('SID');
	session_start();
	setcookie(session_name(), '', 100);
	session_unset();
	session_destroy();
	$_SESSION = array();
	header("Refresh:0");
} 
?>