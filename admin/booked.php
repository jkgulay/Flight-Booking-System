<?php include 'db_connect.php'; ?>

<div class="container-fluid pt-3">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<h4 class="card-title"><b>Booked Flights List</b></h4>
			</div>
			<div class="card-body">
				<table class="table table-striped table-bordered" id="flight-list">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-center">Information</th>
							<th class="text-center">Flight Info</th>
							<th class="text-center">Status</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$airport = $conn->query("SELECT * FROM airport_list ");
						while ($row = $airport->fetch_assoc()) {
							$aname[$row['id']] = ucwords($row['airport'] . ', ' . $row['location']);
						}
						$i = 1;
						$qry = $conn->query("SELECT b.*, f.*, a.airlines, a.logo_path, b.id as bid FROM booked_flight b INNER JOIN flight_list f ON f.id = b.flight_id INNER JOIN airlines_list a ON f.airline_id = a.id ORDER BY b.id DESC");
						while ($row = $qry->fetch_assoc()):
						?>
							<tr>
								<td><?php echo $i++ ?></td>
								<td>
									<p>Name: <b><?php echo $row['name'] ?></b></p>
									<p><small>Contact #: <b><?php echo $row['contact'] ?></b></small></p>
									<p><small>Address: <b><?php echo $row['address'] ?></b></small></p>
								</td>
								<td>
									<div class="row">
										<div class="col-sm-4">
											<img src="../assets/img/<?php echo $row['logo_path'] ?>" alt="" class="img-fluid">
										</div>
										<div class="col-sm-8">
											<p>Airline: <b><?php echo $row['airlines'] ?></b></p>
											<p><small>Plane: <b><?php echo $row['plane_no'] ?></b></small></p>
											<p><small>Location: <b><?php echo $aname[$row['departure_airport_id']] . ' - ' . $aname[$row['arrival_airport_id']] ?></b></small></p>
											<p><small>Departure: <b><?php echo date('M d,Y h:i A', strtotime($row['departure_datetime'])) ?></b></small></p>
											<p><small>Arrival: <b><?php echo date('M d,Y h:i A', strtotime($row['arrival_datetime'])) ?></b></small></p>
										</div>
									</div>
								</td>
								<td class="text-center">
									<span class="badge badge-<?php echo $row['status'] == 'accepted' ? 'success' : ($row['status'] == 'declined' ? 'danger' : 'warning'); ?>">
										<?php echo ucwords($row['status']) ?>
									</span>
								</td>
								<td class="text-center">
									<button class="btn btn-outline-primary btn-sm edit_booked" type="button" data-id="<?php echo $row['bid'] ?>"><i class="fa fa-edit"></i></button>
									<button class="btn btn-outline-danger btn-sm delete_booked" type="button" data-id="<?php echo $row['bid'] ?>"><i class="fa fa-trash"></i></button>
									<?php if ($row['status'] == 'pending'): ?>
										<button class="btn btn-success btn-sm accept_booking" type="button" data-id="<?php echo $row['bid'] ?>"><i class="fa fa-check"></i></button>
										<button class="btn btn-warning btn-sm decline_booking" type="button" data-id="<?php echo $row['bid'] ?>"><i class="fa fa-times"></i></button>
									<?php endif; ?>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<style>
	td p {
		margin: unset;
	}

	td img {
		width: 8vw;
		height: 12vh;
	}

	td {
		vertical-align: middle !important;
	}

	.badge {
		font-size: 0.9em;
	}
</style>

<script>
	$(document).ready(function() {
		$('#flight-list').dataTable();

		$('#new_booked').click(function() {
			uni_modal("New Flight", "manage_booked.php", 'mid-large');
		});

		$('.edit_booked').click(function() {
			uni_modal("Edit Information", "manage_booked.php?id=" + $(this).attr('data-id'), 'mid-large');
		});

		$('.delete_booked').click(function() {
			_conf("Are you sure to delete this data?", "delete_booked", [$(this).attr('data-id')]);
		});

		$('.accept_booking').click(function() {
			const id = $(this).attr('data-id');
			_conf("Are you sure you want to accept this booking?", "accept_booking", [id]);
		});

		$('.decline_booking').click(function() {
			const id = $(this).attr('data-id');
			_conf("Are you sure you want to decline this booking?", "decline_booking", [id]);
		});
	});

	function delete_booked($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_flight',
			method: 'POST',
			data: {
				id: $id
			},
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Flight successfully deleted", 'success');
					setTimeout(function() {
						location.reload();
					}, 1500);
				}
			}
		});
	}

	function accept_booking($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=update_booking_status',
			method: 'POST',
			data: {
				id: $id,
				status: 'accepted'
			},
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Booking successfully accepted", 'success');
					setTimeout(function() {
						location.reload();
					}, 1500);
				}
			}
		});
	}

	function decline_booking($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=update_booking_status',
			method: 'POST',
			data: {
				id: $id,
				status: 'declined'
			},
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Booking successfully declined", 'success');
					setTimeout(function() {
						location.reload();
					}, 1500);
				}
			}
		});
	}
</script>