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
	<?php
	if (isset($json['title'])) {
	?>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg">
			</div>
			<div class="col-lg-8 bg-light px-0">
				<div class="row pt-1 bg-white"></div>
				<div class="row mt-5">
					<div class="col">
					</div>
					<div class="col-10">
						<div class="row">
							<h3 class="col float-start align-self-end mx-2 px-0"><?php echo htmlspecialchars($json['title']); ?></h3>
							<?php
							if ((isset($_SESSION['scope'])) and (($_SESSION['scope'] == 'TA-Administrator') or ($_SESSION['scope'] == 'TA-Librarian'))) { ?>
							<button class="float-start btn btn-primary align-self-end" style="width: 127px;" data-bs-toggle="modal" data-bs-target="#editBook" id="editModal">Edit Book</button>
							<div class="modal fade" id="editBook">
								<div class="modal-dialog modal-lg modal-dialog-centered">
									<div class="modal-content">
										<div class="modal-header">
											<h2> Edit Book </h2>
											<button type="button" class="btn-close" data-bs-dismiss="modal">
											</button>
										</div>
										<div class="modal-body">
											<form action="/edit_book?book_id=<?php echo urlencode($json['book_id']); ?>" method="post">
												<div class="mt-2">
													<label for="title" class="form-label">Title</label>
													<textarea class="form-control" maxlength="200" name="title" rows="2" style="resize: none;" placeholder="Maxiumum length of 200 characters" required><?php echo htmlspecialchars($json['title']); ?></textarea>
												</div>
												<div class="mt-2">
													<?php if (isset($json['authors'])) { ?>
													<label for="authors" class="form-label">Authors (Optional. If more than one, divide with commas.)</label>
													<input type="text" maxlength="200" class="form-control" name="authors" placeholder="Maxiumum length of 100 characters" value="<?php echo htmlspecialchars($json['authors']); ?>">
													<?php } else { ?>
													<label for="authors" class="form-label">Authors (Optional. If more than one, divide with commas.)</label>
													<input type="text" maxlength="200" class="form-control" name="authors" placeholder="Maxiumum length of 100 characters">
													<?php }?>
												</div>
												<div class="mt-2">
													<label for="description" class="form-label">Description</label>
													<textarea class="form-control" maxlength="10000" name="description" rows="5" style="resize: none;" placeholder="Maxiumum length of 10,000 characters" required><?php echo htmlspecialchars($json['description']); ?></textarea>
												</div>
												<div class="mt-2">
													<?php if (isset($json['edition'])) { ?>
													<label for="edition" class="form-label">Edition (Optional)</label>
													<input type="text" maxlength="50" class="form-control" name="edition" placeholder="Maximum length of 50 characters" value="<?php echo htmlspecialchars($json['edition']); ?>">
													<?php } else { ?>
													<label for="edition" class="form-label">Edition (Optional)</label>
													<input type="text" maxlength="50" class="form-control" name="edition" placeholder="Maximum length of 50 characters">
													<?php }?>
												</div>
												<div class="mt-2">
													<label for="publisher" class="form-label">Publisher</label>
													<input type="text" maxlength="200" class="form-control" name="publisher" placeholder="Maximum length of 200 characters" value="<?php echo htmlspecialchars($json['publisher']); ?>" required>
												</div>
												<div class="mt-2">
													<label for="isbn13" class="form-label">ISBN 13</label>
													<input type="text" maxlength="25" class="form-control" name="isbn13" placeholder="Maximum length of 25 characters" value="<?php echo htmlspecialchars($json['ISBN13']); ?>" required>
												</div>
												<div class="mt-2">
													<?php if (isset($json['ISBN10'])) { ?>
													<label for="isbn10" class="form-label">ISBN 10 (Optional)</label>
													<input type="text" maxlength="25" class="form-control" name="isbn10" placeholder="Maximum length of 25 characters" value="<?php echo htmlspecialchars($json['ISBN10']); ?>">
													<?php } else { ?>
													<label for="isbn10" class="form-label">ISBN 10 (Optional)</label>
													<input type="text" maxlength="25" class="form-control" name="isbn10" placeholder="Maximum length of 25 characters" >
													<?php }?>
												</div>
												<div class="mt-2">
													<?php if (isset($json['ISBN10'])) { ?>
													<label for="issn" class="form-label">ISSN (Optional)</label>
													<input type="text" maxlength="8" class="form-control" name="issn" placeholder="Maximum length of 8 numbers" value="<?php echo htmlspecialchars($json['ISSN']); ?>">
													<?php } else { ?>
													<label for="issn" class="form-label">ISSN (Optional)</label>
													<input type="text" maxlength="8" class="form-control" name="issn" placeholder="Maximum length of 8 numbers">
													<?php }?>
												</div>
												<div class="modal-footer mt-4">
													<button type="submit" class="btn btn-primary" formmethod="post">Edit Book</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							<?php }
							?>
						</div>
						<div class="row bg-dark pt-1 mt-2">
							<h5 class="text-white"> Description </h5>
						</div>
						<div class="row bg-white pt-1 mt-2">
							<p class="small"><?php echo htmlspecialchars($json['description']); ?></p>
						</div>
						<div class="row bg-dark pt-1 mt-4">
							<h5 class="text-white"> General Information </h5>
						</div>
						<div class="row py-2 bg-white">
							<ul class="list-group list-group-flush">
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">Author(s)</p></div>
										<?php echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['authors']).'</p></div>' ?>
									</div>
								</li>
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">Publisher</p></div>
										<?php echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['publisher']).'</p></div>' ?>
									</div>
								</li>
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">ISBN13</p></div>
										<?php echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['ISBN13']).'</p></div>' ?>
									</div>
								</li>
								<?php 
								if (isset($json['ISBN10'])) { ?>
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">ISBN10</p></div>
										<?php
											echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['ISBN10']).'</p></div>';
										?>
									</div>
								</li>
								<?php }?>
								<?php 
								if (isset($json['ISSN'])) { ?>
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">ISSN</p></div>
										<?php
											echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['ISSN']).'</p></div>';
										?>
									</div>
								</li>
								<?php }?>
								<?php 
								if (isset($json['edition'])) { ?>
								<li class="list-group-item">
									<div class="row">
										<div class="col"><p class="my-0 py-0">Edition</p></div>
										<?php
											echo '<div class="col"><p class="my-0 py-0 float-end">'.htmlspecialchars($json['edition']).'</p></div>';
										?>
									</div>
								</li>
								<?php }?>
							</ul>
						</div>
					</div>
					<div class="col">
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
		<h1>Book not found.</h1>
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