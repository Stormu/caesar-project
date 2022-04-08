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
	curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/books/?book_id=".$_GET['book_id']);
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
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg">
			</div>
			<div class="col-lg-8 bg-white px-0">
			<h1 class="text-center mt-2">Privacy Policy</h1>
			<p class="mt-2">This privacy policy ("policy") will help you understand how The Alexandria ("us", "we", "our") uses and protects the data you provide to us when you 
			visit and use The Alexandria System ("website", "service"). </p>
			<p>We are committed to protecting the privacy and accuracy of confidential information to the extent possible, subject to provisions of state and federal
			law. Other than as required by laws that guarantee public access to certain types of information, or in response to subpoenas or other legal instruments 
			that authorize access, personal information is not actively shared. In particular, we do not re-distribute or sell personal information collected on our web servers. </p>
			<h3>Information collected: </h3>
			<p>The Alexandria receives and stores any information you knowingly provide to us when you create an account, publish content,  or fill any forms on the Website. When required,
			 this information may include the following:</p>
			<ul>
			<li>Account details (such as user name, unique user ID, password, etc)</li>
			<li>Contact information (such as email address, phone number, etc) </li>
			<li>Basic personal information (such as your name) </li>
			<li>Any other materials you willingly submit to us (such as comments, images, etc)</li>
			</ul>
			<p>You reserve the right not to share the listed information but risk losing access to our services should any information be mandatory. You may contact the administrators of The 
			Alexandria to find out what information is mandatory.</p>
			<p>We do not knowingly collect any information from users who are below the age of 13. If you are under 13, we request that you do not share any personal information with our service. If you believe any user is under the age of 13, please contact the administrators so we may remove the user’s personal information.</p>
			<h3>Cookies</h3>
			<p>The Alexandria site may use "cookies" in order to deliver web content specific to individual users' interests or to keep track of online purchasing transactions. Sensitive personal
			 information is not stored within cookies. Please note that you have the ability to accept or decline cookies. Most web browsers automatically accept cookies by default, but you can
			 modify your browser settings to decline cookies if you prefer. </p>


			<h3>Use of collected information:</h3>
			<p>We collect personal information in order to carry out our services to you. If you decline to share personal information with us, you will be restricted from being able to take full advantage of our service. Personal information is collected for the following purposes:</p>
			<ul>
			<li>Create and manage user accounts.</li>
			<li>Improve user experience.</li>
			<li>Protect from abuse and malicious users.</li>
			<li>Run and operate the Website and Services.</li>
			<li>Respond to inquiries and offer support.</li>
			</ul>
			<h3>Distribution of collected information:</h3>
			<p>The Alexandria will not disclose, without your consent, personal information collected about you, except for certain explicit circumstances in which disclosure is required by law.</p>
			<p>The Alexandria will not distribute or sell personal information to third-party organizations.</p>
			<h3>Privacy Statement Revisions:</h3>
			<p>This Privacy Statement was last revised on 18 November 2021. We may change this Privacy Statement at any time and for any reason. We encourage you to review this Privacy Statement each time you visit the website.</p>
			<p>If we make a significant change to our Privacy Statement, we will post a notice on the homepage of our web site for a period of time after the change is made. </p>
			<h3>Responsibility for External Sites:</h3>
			<p>This website may contain links to other web sites. Some of those web sites may be operated by third parties. We provide the links for your convenience, but we do not review,
			 control, or monitor the privacy practices of websites operated by others. </p>

			<p>We are not responsible for the performance of websites operated by third parties or for your business dealings with them. Therefore, whenever you leave this website we recommend 
			that you review each website's privacy practices and make your own conclusions regarding the adequacy of these practices.</p>
			<h3>Restricting the Collection of your Personal Data:</h3>
			<p>At some point, you might wish to restrict the use and collection of your personal data. You can achieve this by doing the following:</p>
			<p>When you are filling the forms on the website, make sure to check if there is a box which you can leave unchecked, if you don't want to disclose your personal information.</p>
			<p>If you have already agreed to share your information with us, feel free to contact us via email and we will be more than happy to change this for you.</p>
			<p>The Alexandria will not lease, sell or distribute your personal information to any third parties, unless we have your permission. We might do so if the law forces us. We will retain and 
			use your Personal Information for the period necessary as long as your user account remains active, to enforce our agreements, resolve disputes, and unless a longer retention period is required or 
			permitted by law. By using our service, you agree to follow the terms of service laid out and to be bound to this policy and any future updates amended.</p>

			</div>
			<div class="col-lg">
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