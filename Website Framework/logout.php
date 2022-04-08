<?php
	session_name('SID');
	session_start();
	setcookie(session_name(), '', 100);
	setcookie('APP_AT', '', 100);
	$ch = curl_init();
	$authorization = "Authorization: Bearer ".$_COOKIE['APP_RT'];
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/token/blacklist");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	setcookie('APP_RT', '', 100);
	session_unset();
	session_destroy();
	$_SESSION = array();
	header("Location: index");
?>
