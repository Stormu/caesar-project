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
	if(isset($_SESSION['username'])) {
		header("location: index");
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
		<div class="container-fluid" style="height:100%;">
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
													<p class="fw-light text-danger" href="">Login Failed.</p>
													<p class="fw-light text-danger" href="">Warning: Existing accounts will be locked for 5 minutes.</p>
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
													<a class="fst-italic fw-light text-decoration-none" href="">Don't have an account yet? Sign up!</a>
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
		<div class="row">
			<div class="col bg-white">
			</div>
			<div class="col-lg-7">
				<div class="row mt-1">
				</div>
				<div class="row bg-dark">
					<h2 class="col text-white mt-2 text-center">Account Registration</h2>
				</div>
				<div class="row bg-light">
					<div class="col"></div>
					<div class="col-9">
						<form action="/new_user" method="post">
							<h4 class="mt-5">Username</h4>
							<p class="mt-1"> New usernames can only consist of letters, numbers, and underscores. It must also be 3 to 15 characters long.</p>
							<?php if (isset($_SESSION['username_invalid'])) { ?>
							<p class="mt-1 fw-light text-danger"> Invalid new username. Please make sure your new username matches the requirments stated above.</p>
							<?php } else if (isset($_SESSION['username_taken'])) {?>
							<p class="mt-1 fw-light text-danger"> Username is already in use.</p>
							<?php unset($_SESSION['username_taken']);
							}?>
							<div class="mt-2">
								<label for="username" class="form-label">New Username</label>
								<input type="text" class="form-control" name="username" placeholder="Enter Username" minlength="3" maxlength="15" required>
							</div>
							<h4 class="mt-4">Email</h4>
							<p class="mt-1"> An email is required for account registration</p>
							<?php if (isset($_SESSION['email_invalid'])) { ?>
							<p class="mt-1 fw-light text-danger"> No changes made. Invalid email. Please make sure your email contains an '@' and a '.' in the address.</p>
							<?php } else if (isset($_SESSION['email_taken'])) {?>
							<p class="mt-1 fw-light text-danger"> Email is already in use.</p>
							<?php unset($_SESSION['email_taken']);
							}?>
							<div class="mt-2">
								<label for="email" class="form-label">Email Address</label>
								<input type="email" class="form-control" name="email" placeholder="Enter email address" required>
							</div>
							<h4 class="mt-4">Password</h4>
							<p class="mt-1">A new password must be 12 to 50 characters long and must include:</p>
							<ul class="ms-2">
								<li>At least one number</li>
								<li>At least one lowercase letter</li>
								<li>At least one uppercase letter</li>
								<li>One of the following characters: ! - ? $ # </li>
							</ul>
							<div class="mt-2">
								<label for="password" class="form-label">New Password</label>
								<input type="password" class="form-control" name="password" placeholder="New password" minlength="12" maxlength="50" required>
							</div>
							<div class="mt-4">
								<button type="submit" class="btn btn-primary float-end mb-4" formmethod="post">Create Account</button>
							</div>
						</form>
					</div>
					<div class="col"></div>
				</div>
			</div>
			<div class="col bg-white">
			</div>
		</div>
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