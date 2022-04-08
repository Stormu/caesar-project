<?php
	function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
	}

	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	$ignore = False;
	if (!isset($_POST["body"])) {
		$ignore = True;
		header("location: forums");
	}
	//Test validation of inputs
	if (!preg_match("/^.{0,300}$/",$_POST["body"]) and (isset($_POST["body"]))) {
		$ignore = True;
		header("location: forums");
	}
	if ($ignore == False) {
		//Create topic in database
		$ch = curl_init();
		$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/forum/post?topic_id=".urlencode($_GET["topic_id"])."&body=".urlencode($_POST["body"]));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		
		curl_close ($ch);
		
		if ($httpcode == 201) {
			header("location: ".$_SERVER['HTTP_REFERER']);
		}
		else {
			header("location: ".$_SERVER['HTTP_REFERER']);
		}
	}
?>