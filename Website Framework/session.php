<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	//cURL request to login to API 
	if (isset($_SESSION['new_account'])) {
		$data = array('username' => $_SESSION['new_username'], 'password' => $_SESSION['new_password']);
		unset($_SESSION['new_username']);
		unset($_SESSION['new_password']);
	} else {
		$data = array('username' => $_POST["username"], 'password' => $_POST["password"]);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/token/");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	curl_close ($ch);

	$json = json_decode($response, true);

	if (isset($json['access_token'])) {
		//use recieved access token to request user data
		setcookie("APP_AT", $json['access_token'], time()+60*60, "/", httponly:true );
		if(isset($json['refresh_token'])) {
			setcookie("APP_RT", $json['refresh_token'], time()+60*60*24*30, "/",httponly:true );
		}
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
			//create session for user
			session_name('SID');
			ini_set("session.cookie_httponly", True);
			session_start();
			if (isset($json['login_failed'])) {
				unset($_SESSION['login_failed']);
			}
			if (isset($json['locked_account'])) {
				unset($_SESSION['locked_account']);
			}
			$_SESSION['loggedin'] = true;
			$_SESSION['UID'] = $json['UID'];
			$_SESSION['username'] = $json['username'];
			$_SESSION['img_url'] = $json['img_url'];
			$_SESSION['scope'] = $json['scope'];
			$_SESSION['privacy'] = $json['public'];
			if (isset($_SESSION['new_account'])) {
				header("location: user/".urlencode($_SESSION['username']));
			} else {
				header("location:".$_SERVER['HTTP_REFERER']);
			}
		} 
	} 
	else {
		if ($json['detail'] == 'Locked Account.') {
			session_name('SID');
			ini_set("session.cookie_httponly", True);
			session_start();	
			$_SESSION['locked_account'] = true;
			header("location:".$_SERVER['HTTP_REFERER']);
		}
		else {
			session_name('SID');
			ini_set("session.cookie_httponly", True);
			session_start();	
			$_SESSION['login_failed'] = true;
			header("location:".$_SERVER['HTTP_REFERER']);
		}
	}
?>