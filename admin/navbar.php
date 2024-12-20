<?php
$user_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null;
$user_type = isset($_SESSION['type']) ? $_SESSION['type'] : null;
?>

<nav id="sidebar" class='mx-lt-5 bg-dark pt-3'>
	<div class="sidebar-list">
		<a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-home"></i></span> Home</a>
		<a href="index.php?page=view_flight" class="nav-item nav-view_flight"><span class='icon-field'><i class="fa fa-plane-departure"></i></span> Available Flights</a>


		<?php if ($user_type == 1):  ?>
			<a href="index.php?page=admin_panel" class="nav-item nav-admin_panel"><span class='icon-field'><i class="fa fa-user-md"></i></span> Admin Panel</a>
			<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users"></i></span> Users</a>
			<a href="index.php?page=booked" class="nav-item nav-booked"><span class='icon-field'><i class="fa fa-book"></i></span> Booked</a>
			<a href="index.php?page=flights" class="nav-item nav-flights"><span class='icon-field'><i class="fa fa-plane-departure"></i></span> Flights</a>
			<a href="index.php?page=airport" class="nav-item nav-airport"><span class='icon-field'><i class="fa fa-map-marked-alt"></i></span> Airport</a>
			<a href="index.php?page=airlines" class="nav-item nav-airlines"><span class='icon-field'><i class="fa fa-building"></i></span> Airlines</a>
		<?php endif; ?>

		<?php if ($user_type == 2):  ?>
			<a href="index.php?page=booked" class="nav-item nav-booked"><span class='icon-field'><i class="fa fa-book"></i></span> Booked</a>
			<a href="index.php?page=flights" class="nav-item nav-flights"><span class='icon-field'><i class="fa fa-plane-departure"></i></span> Flights</a>
			<a href="index.php?page=airport" class="nav-item nav-airport"><span class='icon-field'><i class="fa fa-map-marked-alt"></i></span> Airport</a>
			<a href="index.php?page=airlines" class="nav-item nav-airlines"><span class='icon-field'><i class="fa fa-building"></i></span> Airlines</a>
		<?php endif; ?>
	</div>
</nav>

<script>
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : ''; ?>').addClass('active');
</script>