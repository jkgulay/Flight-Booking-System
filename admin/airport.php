<?php include('db_connect.php'); ?>

<div class="container-fluid pt-3">
	<div class="row">
		<!-- Form Panel -->
		<div class="col-md-4">
			<div class="card shadow-sm">
				<div class="card-header text-white d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">
						<i class="fas fa-plane-departure mr-2"></i>Airport Management
					</h5>
				</div>
				<form action="" id="manage-airports" autocomplete="off">
					<div class="card-body">
						<input type="hidden" name="id">
						<div class="form-group">
							<label class="control-label">
								<i class="fas fa-building mr-2"></i>Airport Name
							</label>
							<textarea
								name="airport"
								cols="30"
								rows="2"
								class="form-control"
								placeholder="Enter airport name"
								required></textarea>
						</div>
						<div class="form-group">
							<label class="control-label">
								<i class="fas fa-map-marker-alt mr-2"></i>Location
							</label>
							<textarea
								name="location"
								cols="30"
								rows="2"
								class="form-control"
								placeholder="Enter airport location"
								required></textarea>
						</div>
					</div>
					<div class="card-footer">
						<div class="row">
							<div class="col-md-12 d-flex justify-content-between">
								<button type="submit" class="btn btn-primary btn-sm">
									<i class="fas fa-save mr-2"></i>Save
								</button>
								<button type="button" class="btn btn-secondary btn-sm" onclick="_reset()">
									<i class="fas fa-times mr-2"></i>Cancel
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Table Panel -->
		<div class="col-md-8">
			<div class="card shadow-sm">
				<div class="card-header text-white d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">
						<i class="fas fa-list mr-2"></i>Airport List
					</h5>
					<div class="card-tools">
						<button class="btn btn-success btn-sm" id="refresh-table">
							<i class="fas fa-sync-alt mr-2"></i>Refresh
						</button>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover" id="airportTable">
							<thead class="thead-light">
								<tr>
									<th class="text-center">#</th>
									<th>Airport</th>
									<th>Location</th>
									<th class="text-center">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$airports = $conn->query("SELECT * FROM airport_list ORDER BY id ASC");
								while ($row = $airports->fetch_assoc()):
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td>
										<td>
											<div class="d-flex align-items-center">

												<?php echo htmlspecialchars($row['airport']) ?>
											</div>
										</td>
										<td><?php echo htmlspecialchars($row['location']) ?></td>
										<td class="text-center">
											<div class="btn-group" role="group">
												<button
													class="btn btn-sm btn-warning edit_airline"
													data-id="<?php echo $row['id'] ?>"
													data-airport="<?php echo htmlspecialchars($row['airport']) ?>"
													data-location="<?php echo htmlspecialchars($row['location']) ?>"
													title="Edit">
													<i class="fas fa-edit"></i>
												</button>
												<button
													class="btn btn-sm btn-danger delete_airline"
													data-id="<?php echo $row['id'] ?>"
													title="Delete">
													<i class="fas fa-trash-alt"></i>
												</button>
											</div>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	body {
		background-color: #f4f6f9;
	}

	.card {
		border-radius: 10px;
		margin-bottom: 20px;
	}

	.card-header {
		padding: 15px;
		border-bottom: 1px solid rgba(0, 0, 0, 0.1);
		background-color: #213555;
	}

	.table td,
	.table th {
		vertical-align: middle !important;
		padding: 12px;
	}

	.airport-icon {
		font-weight: bold;
	}
</style>

<script>
	$(document).ready(function() {
		// DataTable initialization
		$('#airportTable').DataTable({
			responsive: true,
			language: {
				searchPlaceholder: "Search airports...",
				lengthMenu: "Show _MENU_ entries"
			},
			columnDefs: [{
				targets: [-1],
				orderable: false
			}]
		});

		// Reset form function
		function _reset() {
			$('#manage-airports')[0].reset();
			$('[name="id"]').val('');
		}

		// Form submission
		$('#manage-airports').on('submit', function(e) {
			e.preventDefault();

			// Validate form
			if (!validateForm()) return;

			Swal.fire({
				title: 'Processing...',
				text: 'Saving airport details',
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: 'ajax.php?action=save_airports',
				method: 'POST',
				data: new FormData(this),
				processData: false,
				contentType: false,
				success: function(resp) {
					if (resp == 1) {
						Swal.fire({
							icon: 'success',
							title: 'Airport Added',
							text: 'Airport details successfully saved',
							timer: 1500,
							showConfirmButton: false
						}).then(() => location.reload());
					} else if (resp == 2) {
						Swal.fire({
							icon: 'success',
							title: 'Airport Updated',
							text: 'Airport details successfully updated',
							timer: 1500,
							showConfirmButton: false
						}).then(() => location.reload());
					}
				}
			});
		});

		// Edit airport
		$('.edit_airline').click(function() {
			var cat = $('#manage-airports');
			cat.get(0).reset();
			cat.find("[name='id']").val($(this).data('id'));
			cat.find("[name='airport']").val($(this).data('airport'));
			cat.find("[name='location']").val($(this).data('location'));
		});

		// Delete airport
		$('.delete_airline').click(function() {
			var id = $(this).data('id');
			Swal.fire({
				title: 'Are you sure?',
				text: "You won't be able to revert this!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!'
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: 'ajax.php?action=delete_airports',
						method: 'POST',
						data: {
							id: id
						},
						success: function(resp) {
							if (resp == 1) {
								Swal.fire('Deleted!', 'Airport has been deleted.', 'success').then(() => location.reload());
							}
						}
					});
				}
			});
		});

		// Validate form
		function validateForm() {
			var airport = $('[name="airport"]').val().trim();
			var location = $('[name="location"]').val().trim();
			if (airport === '' || location === '') {
				Swal.fire('Error', 'Please fill in all fields.', 'error');
				return false;
			}
			return true;
		}
	});
</script>