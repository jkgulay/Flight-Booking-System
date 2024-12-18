<?php
include('db_connect.php');
$qry = $conn->query("SELECT * FROM booked_flight WHERE id = " . $_GET['id']);
foreach ($qry->fetch_array() as $k => $v) {
	$$k = $v;
}
?>
<div class="container-fluid">
	<div class="col-lg-12">
		<form action="" id="book-flight">
			<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
			<div class="row">
				<div class="col-md-6">
					<label class="control-label">Name</label>
					<input type="text" name="name" class="form-control" value="<?php echo $name ?>" required>
				</div>
				<div class="col-md-6">
					<label class="control-label">Contact Number</label>
					<input type="text" name="contact" class="form-control" value="<?php echo $contact ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-md-12">
					<label class="control-label">Address</label>
					<textarea name="address" id="" cols="30" rows="2" class="form-control" required><?php echo $address ?></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center">
					<button class="btn btn-primary btn-sm" type="submit">Save</button>
					<button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$('#book-flight').submit(function(e) {
		e.preventDefault();
		start_load();

		$.ajax({
			url: 'ajax.php?action=update_booked',
			method: 'POST',
			data: $(this).serialize(),
			success: function(resp) {
				end_load();
				if (resp == 1) {
					$('.modal').modal('hide');
					alert_toast('Booked Flight successfully updated.', 'success');
					setTimeout(() => location.reload(), 1500);
				} else {
					alert_toast('An error occurred while updating the booking.', 'danger');
				}
			},
			error: function(xhr, status, error) {
				end_load();
				console.error('Error:', status, error);
				alert_toast('An error occurred while processing your request.', 'danger');
			}
		});
	});
</script>

<style>
	#uni_modal .modal-footer {
		display: none;
	}
</style>