<?php
include('db_connect.php');

// Updated query
$qry = $conn->query("
    SELECT b.*, 
           f.flight_id, 
           f.departure_airport, 
           f.arrival_airport, 
           f.departure_datetime, 
           f.arrival_datetime, 
           f.price, 
           f.airlines 
    FROM booked_flight b 
    INNER JOIN flight_details f ON f.flight_id = b.flight_id 
    WHERE b.status = 'pending' 
    ORDER BY b.id DESC
");
?>

<div class="container-fluid pt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><b>Manage Pending Bookings</b></h4>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered" id="booking-list">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer Information</th>
                            <th>Flight Information</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $qry->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <p>Name: <b><?php echo $row['name']; ?></b></p>
                                    <p>Contact #: <b><?php echo $row['contact']; ?></b></p>
                                    <p>Address: <b><?php echo $row['address']; ?></b></p>
                                </td>
                                <td>
                                    <p>Airlines: <b><?php echo $row['airlines']; ?></b></p>
                                    <p>Origin: <b><?php echo $row['departure_airport']; ?></b></p>
                                    <p>Destination: <b><?php echo $row['arrival_airport']; ?></b></p>
                                    <p>Departure: <b><?php echo date('Y-m-d H:i', strtotime($row['departure_datetime'])); ?></b></p>
                                    <p>Price: <b><?php echo number_format($row['price'], 2); ?></b></p>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning">Pending</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm approve-booking" data-id="<?php echo $row['id']; ?>">Approve</button>
                                    <button class="btn btn-danger btn-sm decline-booking" data-id="<?php echo $row['id']; ?>">Decline</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#booking-list').dataTable();

        $('.approve-booking').click(function() {
            const id = $(this).data('id'); // Get the ID dynamically
            if (confirm("Are you sure you want to approve this booking?")) {
                updateBookingStatus(id, 'approve');
            }
        });

        $('.decline-booking').click(function() {
            const id = $(this).data('id'); // Get the ID dynamically
            if (confirm("Are you sure you want to decline this booking?")) {
                updateBookingStatus(id, 'decline');
            }
        });

        function updateBookingStatus(id, action) {
            $.ajax({
                url: 'approve_booking.php', // Update with the correct PHP script path
                method: 'POST',
                data: {
                    id: id,
                    action: action
                },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    location.reload(); // Reload the page to reflect changes
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        }
    });
</script>
