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
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/forum/topic?topic_id=".$_GET['topic_id']);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
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
	<div class="container-fluid">
	<?php
		if ($httpcode == 200) { 
			//Setting up main topic div
			$json = json_decode($response, true);
		?>
		<div class="row">
			<div class="col-lg">
			</div>
			<div class="col-lg-7">
				<div class="row mt-1">
				</div>
				<div class="row bg-dark">
					<h4 class="text-white col mt-2 text-break"><?php echo htmlspecialchars($json['title'])?></h4>
					<button onclick="location.href='/forums/1';" class="btn btn-outline-light fw-bold float-end my-1 me-1" style="width: 100px;">Forum</button>
				</div>
				<div class="row bg-light">
					<div class="border text-center px-0" style="width: 200px">
						<a href="<?php echo'/user/'.htmlspecialchars($json['creator_username']);?>" class="text-decoration-none text-dark">
						<?php
							if (isset($json['creator_img_url'])) {
								echo '<img class="my-2 img-thumbnail" src="'.$json['creator_img_url'].'" alt="profile_img" style="max-width : 100px">';
							}
							else {
								echo '<img class="my-2 img-thumbnail" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="profile_img" style="max-width : 100px">';
							}
							echo '<h6 class="fw-normal small">'.htmlspecialchars($json['creator_username']).'</h6>';
						?>
						</a>
					</div>
					<div class="col border bg-white">
						<?php echo '<p class="fw-light mt-1 mb-0">Posted on: '."<script>let options = {dateStyle: 'long', timeStyle: 'short'}; document.write(new Date('".$json['date_created']."Z').toLocaleString(undefined, options));</script></p>" ?>
						<div class="row mt-auto text-wrap text-break" style="min-height:100px">
							<h6 class="mb-0 mt-1 fw-normal lh-base"><?php echo htmlspecialchars($json['body'])?></h6>
						</div>
					</div>
				</div>
				<div class="row bg-dark ">
					<h4 class="text-white col mt-2">Posts</h4>
					<?php if (isset($_SESSION['username'])) { ?>
					<button class="btn btn-outline-light fw-bold float-end my-1 me-1" style="width: 100px;"  data-bs-toggle="modal" data-bs-target="#newPost" id="postModal">New Post</button>
					<div class="modal fade" id="newPost">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h2> New Post </h2>
									<button type="button" class="btn-close" data-bs-dismiss="modal">
									</button>
								</div>
								<div class="modal-body">
									<form action="/create_post?topic_id=<?php echo $_GET['topic_id']; ?>" method="post">
										<div class="mt-2">
											<label for="body" class="form-label">Post Body</label>
											<textarea class="form-control" maxlength="300" name="body" rows="17" style="resize: none;" placeholder="Maxiumum length of 300 characters" required></textarea>
										</div>
										<div class="mt-2">
											<p class="fst-italic fw-light">Be sure to follow the forum rules to avoid restriction.</p>
										</div>
										<div class="modal-footer mt-4">
											<button type="submit" class="btn btn-primary" formmethod="post">Post</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<?php
				$offset = $_GET['page']-1;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/forum/posts?topic_id=".$_GET['topic_id']."&offset=".$offset);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response2 = curl_exec($ch);
				$httpcode2 = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
				curl_close($ch);
				
				if ($httpcode2 == 200) {
				$jsons = json_decode($response2, true);
				//Loading data of each post into individual divs 
				foreach ($jsons as $json) { ?>
				<div class="row bg-light">
					<div class="border text-center px-0" style="width: 200px">
						<a href="<?php echo'/user/'.htmlspecialchars($json['creator_username'])?>" class="text-decoration-none text-dark">
							<?php
							if (isset($json['creator_img_url'])) {
								echo '<img class="my-2 img-thumbnail" src="'.$json['creator_img_url'].'" alt="profile_img" style="max-width : 100px">';
							}
							else {
								echo '<img class="my-2 img-thumbnail" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="profile_img" style="max-width : 100px">';
							}
							echo '<h6 class="fw-normal small">'.htmlspecialchars($json['creator_username']).'</h6>'
						?>
						</a>
					</div>
					<div class="col border bg-white">
						<?php echo '<p class="fw-light mt-1 mb-0">Posted on: '."<script>document.write(new Date('".$json['date_created']."Z').toLocaleString(undefined, options));</script></p>" ?>
						<div class="row mt-auto text-wrap text-break" style="min-height:100px">
							<h6 class="mb-0 mt-1 fw-normal lh-base"><?php echo htmlspecialchars($json['body'])?></h6>
						</div>
					</div>
				</div>
				<?php }
				} else {
				?>
				<div class="row bg-light">
					<div class="border text-center px-0" style="height: 150px">
						<p class="mt-5">This topic has no posts.</p>
					</div>
				</div>
				<?php
				}
				?>
			</div>
			<div class="col-lg">
			</div>
		</div>
		<div class="row py-4 bg-white"></div>
	</div>
	<?php } ?>
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