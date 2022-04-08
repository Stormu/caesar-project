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
	if(!isset($_SESSION['username'])) {
		header('Location: index');
	}
?>
<html lang= "en">
<head>
    <meta charset ="utf-8" />
	<meta name = "description" content = "The Alexandria, a Library Management System">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>The Alexandria</title>

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
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link" href="/index">Home</a></li>
					<li class="nav-item"><a class="nav-link" href="/catalog">Catalog</a></li>
					<li class="nav-item"><a class="nav-link" href="/forums">Forums</a></li>
					<?php 
						if(!isset($_SESSION['username']))
						{ ?>
							<li class="nav-item"><button type="button" class="btn btn-primary mr-auto" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
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
					<?php echo '<li class="nav-item"><a class="nav-link active" href="/user/'.$_SESSION['username'].'">Profile</a></li>' ?>
					<li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
    </nav>
	<div class="container-fluid">
		<div class="row">
			<div class="col bg-light">
			</div>
			<div class="col-lg-9 bg-white">
				<div class="row pt-1 bg-light"></div>
				<div class="row pt-3 bg-dark">
					<h2 class="text-white text-center">Edit Profile Information</h2>
				</div>
				<div class="row pt-3 mx-auto col-lg-7">
					<h4 class="">Username</h4>
					<form action="/change_username" method="post">
						<p class="mt-1"> Username changes are only allowed once every 30 days.</p>
						<p class="mt-1"> New usernames can only consist of letters, numbers, and underscores. It must also be 3 to 15 characters long.</p>
						<?php
							if (isset($_SESSION['username_valid_fail'])) {
						?>
								<p class="mt-1 fw-light text-danger"> Invalid new username. Please make sure your new username matches the requirments stated above.</p>
						<?php	unset($_SESSION['username_valid_fail']);
							} else if (isset($_SESSION['username_taken'])) { ?>
								<p class="mt-1 fw-light text-danger"> Username is already taken.</p>
						<?php	unset($_SESSION['username_taken']);
							} else if (isset($_SESSION['username_restricted'])) { ?>
								<p class="mt-1 fw-light text-danger"> It has been less than 30 days since you have last changed your username. Try again in the future.</p>
						<?php	unset($_SESSION['username_restricted']);
							}
							?>
						<div class="row mt-4">
							<div class="" style="width:85px;"><label for="username" class="form-label">Username</label></div>
							<div class="" style="width: 325px;"><input type="text" class="form-control" name="username" placeholder="New Username" maxlength="15" required></div>
							<div class="col-1"></div>
						</div>
						<div class="mt-4">
							<button type="submit" class="btn btn-primary float-end">Change Username</button>
						</div>
					</form>
					<h4 class=" mt-4">General Information</h4>
					<form action="/change_general_info" method="post>
						<p class="mt-1">Change information related to yourself.</p>
						<?php
							if (isset($_SESSION['email_invalid'])) {
						?>
								<p class="mt-1 fw-light text-danger"> No changes made. Invalid email. Please make sure your email contains an '@' and a '.' in the address.</p>
						<?php	unset($_SESSION['email_invalid']);
							} if (isset($_SESSION['first_name_invalid'])) { ?>
								<p class="mt-1 fw-light text-danger"> No changes made. Invalid first name. Make sure you follow the requirements below.</p>
						<?php	unset($_SESSION['first_name_invalid']);
							} if (isset($_SESSION['last_name_invalid'])) { ?>
								<p class="mt-1 fw-light text-danger"> No changes made. Invalid last name. Make sure you follow the requirements below.</p>
						<?php	unset($_SESSION['last_name_invalid']);
							} if (isset($_SESSION['mobile_number_invalid'])) { ?>
								<p class="mt-1 fw-light text-danger"> No changes made. Invalid mobile number. Be sure to follow the requirements below.</p>
						<?php	unset($_SESSION['mobile_number_invalid']);
							}
							?>
						<ul class="list-group list-group-flush mt-4">
							<li class="list-group-item px-0">
								<div class="row mt-2">
									<div class="" style="width:69px;"><label for="email" class="form-label">Email</label></div>
									<div class="" style="width:343px;"><input type="email" class="form-control" name="email" placeholder="Change Email"></div>
								</div>
							</li>
							<li class="list-group-item px-0">
								<div class="row mt-2">
									<p class="mt-1"> Only letters allowed. Max of 20 characters allowed.</p>
									<div class="" style="width:101px;"><label for="first_name" class="form-label">First Name</label></div>
									<div class="" style="width:311px;"><input type="text" class="form-control" maxlength="20" name="first_name" placeholder="Change First Name"></div>
								</div>
							</li>
							<li class="list-group-item px-0">
								<div class="row mt-2">
									<p class="mt-1"> Only letters allowed. Max of 20 characters allowed.</p>
									<div class="" style="width:99px;"><label for="last_name" class="form-label">Last Name</label></div>
									<div class="" style="width:313px;"><input type="text" class="form-control" maxlength="20" name="last_name" placeholder="Change Last Name"></div>
								</div>
							</li>
							<li class="list-group-item px-0">
								<div class="row mt-2">
									<p class="mt-1"> Only numbers are allowed. Max of 15 digits.</p>
									<div class="" style="width:132px;"><label for="mobile_number" class="form-label">Phone Number</label></div>
									<div class="" style="width:280px;"><input type="tel" class="form-control" maxlength="15" name="mobile_number" placeholder="Change Number"></div>
								</div>
							</li>
						</ul>
						<div class="mt-4">
						<button type="submit" class="btn btn-primary float-end" formmethod="post">Submit Changes</button>
						</div>
					</form>
					<h4 class="mt-4">Privacy</h4>
					<form action="/change_privacy" method="post">
						<p class="mt-1">Change whether or not your information is made public.</p>
						<?php
							if ($_SESSION['privacy'] == 1) { ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="privacy" value="1" checked>
								<label class="form-check-label" for="public">Public</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="privacy" value="0">
								<label class="form-check-label" for="private">Private</label>
							</div>
							<?php } else if ($_SESSION['privacy'] == 0) { ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="privacy" value="1">
								<label class="form-check-label" for="public">Public</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="privacy" value="0" checked>
								<label class="form-check-label" for="private">Private</label>
							</div>
						<?php } ?>
						<div class="mt-4">
						<button type="submit" class="btn btn-primary float-end">Submit Changes</button>
						</div>
					</form>
					<h4 class="mt-4">Profile Image</h4>
					<?php 
						if(isset($_SESSION['img_url'])) {
						echo '<img class="img-thumbnail mx-auto mt-4" src="'.$_SESSION['img_url'].'" alt="profile_img" style="max-width : 200px">';
						} else {?>
						<img class="img-thumbnail mx-auto mt-4" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="profile_img" style="max-width : 200px">
						<?php }?>
					<form action="/change_profile_img" method="post">
						<p class="mt-4"> New profile images must be a square image uploaded to <a href="https://imgur.com/">Imgur</a>.</p>
						<p class="mt-1"> Example URL: https://imgur.com/W11BlDY.png </p>
						<?php
							if(isset($_SESSION['image_valid_url'])) { ?>
						<p class="mt-1 fw-light text-danger"> Invalid Imgur url.</p>		
							<?php	unset($_SESSION['image_valid_url']);
							} 
							 else if(isset($_SESSION['restricted'])) { ?>
						<p class="mt-1 fw-light text-danger"> You are not allowed to change your profile image.</p>		
							<?php	unset($_SESSION['restricted']);
							}
							else if(isset($_SESSION['error'])) { ?>
						<p class="mt-1 fw-light text-danger"> An error occured.</p>		
							<?php	unset($_SESSION['error']);
							}
						?>
							<div class="row mt-4">
								<div class="col-3"><label for="img_url" class="form-label">Image URL</label></div>
								<div class="col"><input type="text" class="form-control" name="img_url" placeholder="New Image URL"></div>
								<div class="col-2"></div>
							</div>
						<div class="mt-4">
						<button type="submit" formaction="/reset_profile_img" class="btn btn-secondary float-end ms-2">Clear Image</button>
						<button type="submit" class="btn btn-primary float-end me-2">Change Image</button>
						</div>
					</form>
					<h4 class="mt-4">Security</h4>
					<p class="mt-1">Change your login credentials.</p>
					<p class="mt-1">A new password must be 12 to 50 characters long and must include:</p>
					<ul class="ms-4">
						<li>At least one number</li>
						<li>At least one lowercase letter</li>
						<li>At least one uppercase letter</li>
						<li>One of the following characters: ! - ? $ # </li>
					</ul>
					<?php
							if(isset($_SESSION['password_validation_fail'])) { ?>
						<p class="mt-1 fw-light text-danger"> Invalid new password. Please make sure your new password matches the criteria above.</p>		
							<?php	unset($_SESSION['password_validation_fail']);
							} 
							 else if(isset($_SESSION['incorrect_credentials'])) { ?>
						<p class="mt-1 fw-light text-danger"> Incorrect password.</p>		
							<?php	unset($_SESSION['incorrect_credentials']);
							}
							else if(isset($_SESSION['error_password'])) { ?>
						<p class="mt-1 fw-light text-danger"> An error occured while trying to change your password.</p>		
							<?php	unset($_SESSION['error_password']);
							} else if(isset($_SESSION['password_updated'])) { ?>
						<p class="mt-1 fw-light text-success"> Password updated.</p>		
							<?php	unset($_SESSION['password_updated']);
							}
						?>
					<form action="/change_password" method="post">
						<div class="row mt-4">
							<div class="col-3"><label for="current_password" class="form-label">Current Password</label></div>
							<div class="col"><input type="password" class="form-control" maxlength="50" name="current_password" placeholder="Current Password" required></div>
							<div class="col-2"></div>
						</div>
						<div class="row mt-4">
							<div class="col-3"><label for="new_password" class="form-label">New Password</label></div>
							<div class="col"><input type="password" class="form-control" maxlength="50" name="new_password" placeholder="New Password" required></div>
							<div class="col-2"></div>
						</div>
						<div class="mt-4">
							<button type="submit" class="btn btn-primary float-end">Change Password</button>
						</div>
					</form>
					<div class="row pt-4 bg-white"></div>
				</div>
				<div class="row pt-4 bg-light"></div>
			</div>
			<div class="col bg-light">
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