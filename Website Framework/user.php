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
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/users/?username=".$_GET['username']);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
	
    curl_close($ch);
	
	$json = json_decode($response, true);
?>
<html lang= "en" style="min-height:100%">
<head>
    <meta charset ="utf-8" />
	<meta name = "description" content = "The Alexandria, a Library Management System">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
	if (isset($json['username'])) {
		echo "<title>".$json['username']."'s Profile Page</title>";
	}  
	else {
		echo "<title>The Alexandria</title>";
	}
	?>
	
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
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
					<li class="nav-item"><a class="nav-link" href="/forums">Forums</a></li>
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
					<?php } else { 
							if (substr($_SERVER['REQUEST_URI'], 6) == $_SESSION['username']) {
								echo '<li class="nav-item"><a class="nav-link active" href="/user/'.$_SESSION['username'].'">Profile</a></li>';
							}
							else {
								echo '<li class="nav-item"><a class="nav-link" href="/user/'.$_SESSION['username'].'">Profile</a></li>';
							}
					?>
					<li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</nav>
	<?php
	if (isset($json['username'])) {
	?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg">
			</div>
			<div class="col-lg-9 bg-light px-0">
				<div class="row pt-1 bg-white"></div>
				<?php if (isset($_SESSION['new_account'])) { ?>
				<div class="row mx-0 py-0 border bg-white border-dark">
					<h6 class="text-center text-dark"> Account Successfully Created! </h6>
				</div>
				<?php unset($_SESSION['new_account']);
				} ?>
				<div class="col-10 mx-auto">
					<div class="row py-2">
						<?php 
						if(isset($json['img_url'])) {
						echo '<img class="float-start img-thumbnail col-5" src="'.$json['img_url'].'" alt="profile_img" style="max-width : 200px">';
						} else {?>
						<img class="float-start img-thumbnail col-5" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="profile_img" style="max-width : 200px">
						<?php }
						echo '<h1 class="col align-self-end">'.htmlspecialchars($json['username']).'</h1>' ?>
						<div class="col">
						<?php
							if (isset($_SESSION['username']) and substr($_SERVER['REQUEST_URI'], 6) == $_SESSION['username']) {
								echo '<a href="/edit_profile"><button type="submit" class="float-end btn btn-primary">Edit Profile</button></a>';
							}
						?>
						</div>
					</div>
					<div class="row mt-2 pt-1 bg-dark">
						<h4 class="text-white">User Information</h4>
					</div>
					<div class="row py-2 bg-white">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<div class="row">
									<div class="col"><p class="my-0 py-0">Account Created</p></div>
									<?php echo '<div class="col"><p class="my-0 py-0 float-end">'.DateTime::createFromFormat(DateTime::ISO8601, $json['date_joined'].'Z')->format('F dS, Y').'</p></div>' ?>
								</div>
							</li>
							<?php 
							if (isset($json['first_name']) or isset($json['last_name'])) { ?>
							<li class="list-group-item">
								<div class="row">
									<div class="col"><p class="my-0 py-0">Name</p></div>
									<?php
										$name = '';
										if (isset($json['first_name'])) {
											$name = $name.$json['first_name'].' ';
										}
										if (isset($json['last_name'])) {
											$name = $name.$json['last_name'];
										}
										echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($name).'</p></div>';
									?>
								</div>
							</li>
							<?php }?>
							<?php 
							if (isset($json['email'])) { ?>
							<li class="list-group-item">
								<div class="row">
									<div class="col"><p class="my-0 py-0">Email</p></div>
									<?php
										echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['email']).'</p></div>';
									?>
								</div>
							</li>
							<?php }?>
							
						</ul>
					</div>
					<div class="row pt-4"></div>
					<div class="row pt-1 bg-dark">
						<h4 class="text-white">Lists</h4>
					</div>
					<div class="row py-2 bg-white">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<div class="row">
									<div class="col"><p class="my-0 py-0">This user does not have any lists.</p></div>
									<div class="col"><p class="my-0 py-0 float-end"></p></div>
								</div>
							</li>
						</ul>
					</div>
				</div>
				<div class="row pt-5"></div>
			</div>
			<div class="col-lg">
			</div>
		</div>
		<div class="row py-4 bg-white"></div>
	</div>
	<?php
	}  
	else { ?>
	<div class="container-fluid text-center">
		<h1>User does not exist!</h1>
		<p>Maybe you clicked on a bad link?</p>
	</div>
	<?php }
	?>
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