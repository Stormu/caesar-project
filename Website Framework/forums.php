<!DOCTYPE html>
<?php
	include 'auth_handler.php';
	session_name('SID');
	ini_set("session.cookie_httponly", True);
	session_start();
	if((!isset($_COOKIE['APP_AT'])) and (isset($_COOKIE['APP_RT']))) {
		regenerateAccessToken($_COOKIE['APP_RT']);
	}
	if(isset($_SESSION['username']) and (!isset($_COOKIE['APP_AT'])) and (!isset($_COOKIE['APP_RT'])))
	{
		 auto_logout() ;
	}
?>
<html lang= "en">
<head>
    <meta charset ="utf-8" />
	<meta name = "description" content = "The Alexandria Forums">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
	<title>The Alexandria Forums</title>
	<div class="container mx-1">
		<h1>The Alexandria</h1>
		<p>A Social Library Management System</p>
	</div>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="#!">The Alexandria</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span></button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link" href="/">Home</a></li>
					<li class="nav-item"><a class="nav-link" href="/catalog">Catalog</a></li>
					<li class="nav-item"><a class="nav-link active" href="/forums">Forums</a></li>
					<?php 
						if(!isset($_SESSION['username']))
						{ ?>
							<li class="nav-item"><button type="button" class="btn btn-primary mr-auto" data-bs-toggle="modal" data-bs-target="#loginModal" id="loginModalZ">Login</button>
							<div class="modal fade" id="loginModal">
								<div class="modal-dialog modal-dialog-centered">
									<div class="modal-content">
										<div class="modal-header">
											<h2> Login </h2>
											<button type="button" class="btn-close" data-bs-dismiss="modal">
											</button>
										</div>
										<div class="modal-body">
											<form action="/session" method="post">
												<div class="mt-2">
													<label for="username" class="form-label">Username</label>
													<input type="text" class="form-control" name="username" placeholder="Enter Username" required>
												</div>
												<div class="mt-4">
													<label for="password" class="form-label">Password</label>
													<input type="password" class="form-control" name="password" placeholder="Enter Password" required>
												</div>
												<?php 
												if(isset($_SESSION['login_failed']))
												{ ?>
												<div class="mt-2">
													<p class="fw-light text-danger" href="">Login Failed. Warning: Account will lock after 5 attempts.</p>
												</div>
												<?php
												}
												if(isset($_SESSION['locked_account']))
												{ ?>
												<div class="mt-2">
													<p class="fw-light text-danger" href="">Account has been locked for 5 minutes due to security reasons.</p>
												</div>
												<?php } ?>
												<div class="mt-2">
													<a class="fst-italic fw-light text-decoration-none" href="/registration">Don't have an account yet? Sign up!</a>
												</div>
												<div class="modal-footer mt-4">
													<button type="submit" class="btn btn-primary">Login</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
					<?php } else { ?>
					<?php echo '<li class="nav-item"><a class="nav-link" href="/user/'.$_SESSION['username'].'">Profile</a></li>' ?>
					<li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</nav>
	<div class="container">
		<div class="row">
			<div class="col-lg-2 border mt-1">
				<div class="row pt-2 bg-white"></div>
				<?php 
					if(!isset($_SESSION['username']))
					{ ?>
				<div class="row">
					<div class="row pt-1 bg-white"></div>
						<p class="h6 col-11 mx-auto text-center">An account is required to post new topics.</p>
					<div class="row pt-1 bg-white"></div>
					<button type="button" class="btn btn-primary col-8 mx-auto" disabled>Start New Topic</button>
				</div>
				<?php } else { ?>
				<div class="row">
					<?php if(isset($_SESSION['img_url'])) {
						echo '<img class="float-start col-11 mx-auto img-thumbnail" src="'.$_SESSION['img_url'].'" alt="profile_img" style="max-width : 200px">';
						} else {?>
						<img class="float-start col-11 mx-auto img-thumbnail" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="profile_img" style="max-width : 200px">
						<?php }?>
					<div class="row pt-1 bg-white"></div>
						<?php echo '<p class="h5 col-11 mx-auto text-center">'.$_SESSION['username'].'</p>' ?>
					<div class="row pt-1 bg-white"></div>
					<button type="button" class="btn btn-primary col-8 mx-auto" data-bs-toggle="modal" data-bs-target="#StartNewTopic" id="topicModal">Start New Topic</button>
					<div class="modal fade" id="StartNewTopic">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h2> New Topic </h2>
									<button type="button" class="btn-close" data-bs-dismiss="modal">
									</button>
								</div>
								<div class="modal-body">
									<form action="/create_topic" method="post">
										<div class="mt-2">
											<label for="title" class="form-label">Title</label>
											<input type="text" class="form-control" maxlength="70" name="title" placeholder="Maxiumum length of 70 characters" required>
										</div>
										<div class="mt-4">
											<label for="body" class="form-label">Body</label>
											<textarea class="form-control" maxlength="300" name="body" rows="17" style="resize: none;" placeholder="Maxiumum length of 300 characters" required></textarea>
										</div>
										<div class="mt-2">
											<p class="fst-italic fw-light">Be sure to follow the forum rules to avoid restriction.</p>
										</div>
										<div class="modal-footer mt-4">
											<button type="submit" class="btn btn-primary" formmethod="post">Create Topic</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
				<div class="row pt-1 bg-white"></div>
			</div>
			<div class="col-lg-10 bg-dark px-0">
				<div class="row pt-1 bg-white"></div>
				<h2 class="pt-3 px-2 text-white">The Alexandria Forums</h2>
				<?php
				if ($_GET['page'] == 1) { ?>
				<h4 class="text-white bg-secondary py-2 mb-0 px-2">Pinned Topics</h2>
				<!--Pinned Topics-->
				<div class="list-group rounded-0 bg-light mb-0 pb-auto">
				<?php
				//Grab pinned topics
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/forum/pinned");
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
	
					curl_close($ch);
	
					$jsons = json_decode($response, true);
					foreach ($jsons as $json) { ?>
						<a href="<?php echo '/topics/'.$json['topic_id'].'/1'; ?>" class="border list-group-item p-0">
						<div class="row m-0">
							<div class="col-1 p-0 align-self-center text-center">
								<img src="/assets/chat-box-dark.png" alt="Chat Box" width="45" height="45">
							</div>
							<div class="col-10">
								<?php echo '<h5 class="mb-0 mt-1 fw-normal text-break">'.htmlspecialchars($json['title']).'</h5>' ?>
								<?php echo '<p class="h6 mt-1 small fw-normal"> Posted by: '.htmlspecialchars($json['creator_username']).'</p>' ?>
							</div>
							<div class="col">
							</div>
						</div>
						</a>
				<?php } ?>
				</div>
				<?php } ?>
				<h4 class="text-white bg-secondary py-2 mb-0 px-2">Topics</h2>
				<!--Topics-->
				<div class="list-group rounded-0 bg-light mb-0 pb-auto">
				<?php
				//Grab pinned topics
					$offset = $_GET['page'] - 1;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/forum/topics?offset=".$offset);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
	
					curl_close($ch);
	
					$jsons = json_decode($response, true);
					foreach ($jsons as $json) { ?>
						<a href="<?php echo '/topics/'.$json['topic_id'].'/1'; ?>" class="border list-group-item p-0">
						<div class="row m-0">
							<div class="col-1 p-0 align-self-center text-center">
								<img src="/assets/chat-box-dark.png" alt="Chat Box" width="45" height="45">
							</div>
							<div class="col-9">
								<?php echo '<h5 class="mb-0 mt-1 fw-normal text-break">'.htmlspecialchars($json['title']).'</h5>' ?>
								<?php echo '<p class="h6 mt-1  small fw-normal"> Posted by: '.htmlspecialchars($json['creator_username']).'</p>' ?>
							</div>
							<div class="col align-self-end">
								<?php echo '<p class="h6 small float-end mb-2 mt-0 fw-normal"> Posts: '.$json['posts'].'</p>' ?>
							</div>
						</div>
						</a>
				<?php } ?>
				</div>
			</div>
		</div>
		<div class="row py-4 bg-white"></div>
	</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	<script>
		<?php 
		if ((isset($_SESSION['locked_account'])) or (isset($_SESSION['login_failed']))) { ?>
		var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
		if (localStorage.getItem('modalSet') == 'true') {
			loginModal.show()
		}
		<?php 
			if (isset($_SESSION['login_failed'])) {
				unset($_SESSION['login_failed']);
			}
			if (isset($_SESSION['locked_account'])) {
				unset($_SESSION['locked_account']);
			}
		}	
			
			if (!isset($_SESSION['username'])) { ?>
		var modalChanges = document.getElementById('loginModal')
		modalChanges.addEventListener('hidden.bs.modal', function (event) {
			localStorage.removeItem('modalSet');
		})

		var modalButton = document.getElementById('loginModalZ');
		modalButton.onclick = function() {
			localStorage.setItem('modalSet', 'true');
		}; 
		<?php }
		?>
	</script>
</body>
</html>