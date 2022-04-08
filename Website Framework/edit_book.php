<?php
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	$ignore = False;
	$request = '?book_id='.urlencode($_GET['book_id']);
	if(!isset($_POST["title"]) or !isset($_POST["description"]) or !isset($_POST["publisher"]) or !isset($_POST["isbn13"])) {
		$_SESSION['missing_requirements'] = True;
		header("location: catalog");
		$ignore = True;
	} else {
		if ($_POST["title"] != '') {
			$request .= '&title='.urlencode($_POST["title"]);
		}
		if ($_POST["authors"] != '') {
			$request .= '&authors='.urlencode($_POST["authors"]);
		}
		if ($_POST["description"] != '') {
			$request .= '&description='.urlencode($_POST["description"]);
		}
		if ($_POST["edition"] != '') {
			$request .= '&edition='.urlencode($_POST["edition"]);
		}
		if ($_POST["publisher"] != '') {
			$request .= '&publisher='.urlencode($_POST["publisher"]);
		}
		if ($_POST["isbn13"] != '') {
			$request .= '&ISBN13='.urlencode($_POST["isbn13"]);
		}
		if ($_POST["isbn10"] != '') {
			$request .= '&ISBN10='.urlencode($_POST["isbn10"]);
		}
		if ($_POST["issn"] != '') {
			$request .= '&ISSN='.urlencode($_POST["issn"]);
		}
	}
	
	if($ignore == False) {
		//Add book through API
		$ch = curl_init();
		$authorization = "Authorization: Bearer ".$_COOKIE['APP_AT'];
		curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/books/".$request);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		
		curl_close ($ch);
		
		if ($httpcode == 204) {
			$_SESSION['book_updated'] = True;
			header("location:".$_SERVER['HTTP_REFERER']);
		}
		else {
			$_SESSION['book_failed'] = true;
			header("location:".$_SERVER['HTTP_REFERER']);
		}
	}
	
?>