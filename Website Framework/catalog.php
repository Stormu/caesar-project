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
					<li class="nav-item"><a class="nav-link active" href="/catalog">Catalog</a></li>
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
			<div class="col px-0">
			</div>
			<div class="col-lg-9 px-0">
				<div class="row mt-1 px-0">
				</div>
				<div class="row bg-dark mx-0">
					<?php if (isset($_SESSION['book_added'])) { ?>
					<div class="row mx-0 py-0 border bg-white border-dark">
					<h6 class="text-center text-dark"> Book added!</h6>
					</div>
					<?php unset($_SESSION['book_added']);
					} else if (isset($_SESSION['book_failed'])) {?>
					<div class="row mx-0 py-0 border bg-white border-dark">
					<h6 class="text-center text-dark"> Failed to add book to the catalog.</h6>
					</div>
					<?php unset($_SESSION['book_failed']);
					} else if (isset($_SESSION['missing_requirements'])) {?>
					<div class="row mx-0 py-0 border bg-white border-dark">
					<h6 class="text-center text-dark"> Missing required details for book.</h6>
					</div>
					<?php unset($_SESSION['missing_requirements']);
					} ?>
					<h2 class="col text-white mt-2 mx-0">Library Catalog</h2>
					<?php if ((isset($_SESSION['scope'])) and (($_SESSION['scope'] == 'TA-Administrator') or ($_SESSION['scope'] == 'TA-Librarian'))) { ?>
					<button class="btn btn-outline-light fw-bold float-end my-1 me-1" style="width: 200px;"  data-bs-toggle="modal" data-bs-target="#addBook" id="addModal">Add Book</button>
					<div class="modal fade" id="addBook">
						<div class="modal-dialog modal-lg modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h2> Add Book </h2>
									<button type="button" class="btn-close" data-bs-dismiss="modal">
									</button>
								</div>
								<div class="modal-body">
									<form action="/add_book" method="post">
										<div class="mt-2">
											<label for="title" class="form-label">Title</label>
											<textarea class="form-control" maxlength="200" name="title" rows="2" style="resize: none;" placeholder="Maxiumum length of 200 characters" required></textarea>
										</div>
										<div class="mt-2">
											<label for="authors" class="form-label">Authors (Optional. If more than one, divide with commas.)</label>
											<input type="text" maxlength="200" class="form-control" name="authors" placeholder="Maxiumum length of 100 characters">
										</div>
										<div class="mt-2">
											<label for="description" class="form-label">Description</label>
											<textarea class="form-control" maxlength="10000" name="description" rows="5" style="resize: none;" placeholder="Maxiumum length of 10,000 characters" required></textarea>
										</div>
										<div class="mt-2">
											<label for="edition" class="form-label">Edition (Optional)</label>
											<input type="text" maxlength="50" class="form-control" name="edition" placeholder="Maximum length of 50 characters">
										</div>
										<div class="mt-2">
											<label for="publisher" class="form-label">Publisher</label>
											<input type="text" maxlength="200" class="form-control" name="publisher" placeholder="Maximum length of 200 characters" required>
										</div>
										<div class="mt-2">
											<label for="isbn13" class="form-label">ISBN 13</label>
											<input type="text" maxlength="25" class="form-control" name="isbn13" placeholder="Maximum length of 25 characters" required>
										</div>
										<div class="mt-2">
											<label for="isbn10" class="form-label">ISBN 10 (Optional)</label>
											<input type="text" maxlength="25" class="form-control" name="isbn10" placeholder="Maximum length of 25 characters">
										</div>
										<div class="mt-2">
											<label for="issn" class="form-label">ISSN (Optional)</label>
											<input type="text" maxlength="8" class="form-control" name="issn" placeholder="Maximum length of 8 numbers">
										</div>
										<div class="modal-footer mt-4">
											<button type="submit" class="btn btn-primary" formmethod="post">Add Book</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="row bg-white border mx-0">
					<h5 class="mt-2">Search for books in the catalog</h5>
					<form class="my-3" action="/catalog" method="get">
						<?php if (isset($_GET['title'])) { ?>
						<input type="search" placeholder="Search Title" name="title" class="form-control" style="width: 400px;" value="<?php echo htmlspecialchars($_GET['title']);?>">
						<?php } else {?>
						<input type="search" placeholder="Search Title" name="title" class="form-control" style="width: 400px;">
						<?php } ?>
					</form>
				</div>
				<?php
				if (isset($_GET['title']) and  $_GET['title'] != "") { ?>
				<div class="list-group rounded-0 bg-light mb-0 pb-auto">
				<?php	$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"http://localhost:8000/books/search?title=".urlencode($_GET['title']));
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
					curl_close($ch);
	
					if ($httpcode == 404) { ?>
						<h2 class="text-center py-5 m-0 border">No Books Found</h2>
					<?php } else {
						
					$jsons = json_decode($response, true);
					foreach ($jsons as $json) { ?>
					<a href="<?php echo '/book/'.$json['book_id']; ?>" class="border bg-white list-group-item py-2 p-0">
						<div class="row m-0 px-0 mx-0">
							<div class="p-0 align-self-center text-center" style="width: 150px;">
								<img src="/assets/no-img.png" alt="no image" style="max-width: 125px;">
							</div>
							<div class="col-6">
								<?php echo '<h4 class="mb-0 mt-1 fw-normal text-break">'.htmlspecialchars($json['title']).'</h4>' ?>
								<?php echo '<p class="h6 mt-1  small fw-normal"> Author(s): '.htmlspecialchars($json['authors']).'</p>' ?>
								<?php echo '<p class="h6 small float-start mb-2 mt-0 fw-normal"> Publisher: '.htmlspecialchars($json['publisher']).'</p>' ?>
							</div>
						</div>
					</a>
					<?php } 
					}	?>
				</div>
				<?php } ?>
			</div>
			<div class="col px-0">
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