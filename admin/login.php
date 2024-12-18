<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>Admin | Flight Booking System</title>
	<?php include('./header.php'); ?>
	<?php include('./db_connect.php'); ?>

	<?php
	session_start();
	if (isset($_SESSION['login_id']))
		header("location:index.php?page=home");
	?>
	<style>
		html {
			height: 100%;
		}

		body {
			margin: 0;
			padding: 0;
			font-family: sans-serif;
			background: linear-gradient(#30142b, #2772a1);
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.login-page {
			width: 400px;
			padding: 8% 0 0;
			margin: auto;
		}

		.form {
			position: relative;
			z-index: 1;
			text-align: center;
			position: absolute;
			top: 50%;
			left: 50%;
			width: 400px;
			padding: 40px;
			transform: translate(-50%, -50%);
			background: rgba(0, 0, 0, .5);
			box-sizing: border-box;
			box-shadow: 0 15px 25px rgba(0, 0, 0, .6);
			border-radius: 10px;
		}

		.form input {
			width: 100%;
			padding: 10px 0;
			font-size: 13px;
			color: #fff;
			margin-bottom: 30px;
			border: none;
			border-bottom: 1px solid #fff;
			outline: none;
			background: transparent;
		}

		h2 {
			color: white;
		}

		.form .message {
			margin: 15px 0 0;
			color: #b3b3b3;
			font-size: 12px;
		}

		.form .message a {
			color: #289bb8;
			text-decoration: none;
		}

		.form .register-form {
			display: none;
		}

		.btn {
			position: relative;
			display: inline-block;
			padding: 10px 20px;
			color: #289bb8;
			font-size: 16px;
			text-decoration: none;
			overflow: hidden;
			transition: .5s;
			margin-top: 15px;
			letter-spacing: 2px;
		}

		.btn:hover {
			background: #289bb8;
			color: #fff;
			border-radius: 5px;
			box-shadow: 0 0 5px #289bb8,
				0 0 25px #289bb8,
				0 0 50px #289bb8,
				0 0 100px #289bb8;
		}
	</style>
</head>

<body>
	<div class="container px-3 py-5 px-md-5 text-start text-lg-start my-5">
		<div class="row gx-lg-5 align-items-center mb-5">
			<div class="col-lg-6 mb-5 mb-lg-0" style="z-index: 10; display: flex; flex-direction: column; align-items: flex-start;">


				<h1 style="color: white;">
					To A Better<br />
					<span style="color: hsl(218, 81%, 75%)">Flight Experience</span>
				</h1>
				<p class="mb-4 opacity-70" style="color: #f0f0f0">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Magnam, ullam nisi voluptatibus fuga possimus, illum itaque maxime fugiat, id nam voluptas atque cum distinctio consectetur ut. Ab, nihil? Possimus, delectus?
				</p>
			</div>
			<div class="col-lg-6">
				<div class="login-page">
					<div class="form">
						<form class="register-form" method="POST">
							<h2>Register</h2>
							<input type="text" placeholder="Full Name *" name="name" required />
							<input type="text" placeholder="Username *" name="username" required />
							<input type="email" placeholder="Email *" name="email" required />
							<input type="password" placeholder="Password *" name="password" required />
							<button type="submit" class="btn">Create</button>
							<p class="message">Already registered? <a href="#">Sign In</a></p>
						</form>
						<form class="login-form" method="POST">
							<div class="d-flex justify-content-center mb-3">
								<img src="assets/img/login.png" alt="logo" class="img-fluid" style="max-width: 150px; height: auto;">
							</div>
							<h2>Login</h2>
							<input type="text" placeholder="Username" name="username" required />
							<input type="password" placeholder="Password" name="password" required />
							<button type="submit" class="btn">Sign in</button>
							<p class="message">Not registered? <a href="#">Create an account</a></p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		$('.message a').click(function(e) {
			e.preventDefault(); // Prevent the default anchor behavior
			$('form').animate({
				height: "toggle",
				opacity: "toggle"
			}, "slow");
		});

		// Handle the login form submission
		$('.login-form').submit(function(e) {
			e.preventDefault();
			const $button = $(this).find('button[type="submit"]');
			$button.attr('disabled', true).text('Logging in...');

			$.ajax({
				url: 'ajax.php?action=login',
				method: 'POST',
				data: $(this).serialize(),
				error: function(err) {
					console.log(err);
					$button.removeAttr('disabled').text('Sign in');
				},
				success: function(resp) {
					if (resp == 1) {
						location.href = 'index.php?page=home';
					} else {
						$('.login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
						$button.removeAttr('disabled').text('Sign in');
					}
				}
			});
		});

		$('.register-form').submit(function(e) {
			e.preventDefault();
			const $button = $(this).find('button[type="submit"]');
			$button.attr('disabled', true).text('Creating...');

			$.ajax({
				url: 'ajax.php?action=register',
				method: 'POST',
				data: $(this).serialize(),
				error: function(err) {
					console.log(err);
					$button.removeAttr('disabled').text('Create');
				},
				success: function(resp) {
					if (resp == 1) {
						alert('Registration successful! You can now log in.');
						$('.register-form').hide();
						$('.login-form').show();
					} else {
						$('.register-form').prepend('<div class="alert alert-danger">Registration failed. Please try again.</div>');
						$button.removeAttr('disabled').text('Create');
					}
				}
			});
		});
	</script>
</body>

</html>