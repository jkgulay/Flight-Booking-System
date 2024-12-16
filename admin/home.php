<?php include 'db_connect.php';
$user_count_query = "SELECT COUNT(*) AS total_users FROM users";
$user_count_result = $conn->query($user_count_query);
$user_count = $user_count_result->fetch_assoc()['total_users'];

$booked_flights_query = "SELECT COUNT(*) AS total_booked FROM booked_flight";
$booked_flights_result = $conn->query($booked_flights_query);
$booked_flights = $booked_flights_result->fetch_assoc()['total_booked'];

$available_flights_query = "SELECT COUNT(*) AS total_available FROM flight_list WHERE seats > 0";
$available_flights_result = $conn->query($available_flights_query);
$available_flights = $available_flights_result->fetch_assoc()['total_available'];

$flight_dates_query = "SELECT DISTINCT DATE(departure_datetime) AS flight_date FROM flight_list";
$flight_dates_result = $conn->query($flight_dates_query);
$flight_dates = [];
while ($row = $flight_dates_result->fetch_assoc()) {
    $flight_dates[] = $row['flight_date'];
}
?>
<style>
    body {
        background-color: #f8f9fa;
        margin: 0;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #213555;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .welcome-message {
        font-size: 1.5rem;
        font-weight: 500;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 0.9rem;
        color: #6c757d;
        padding: 10px 0;
        background-color: #f1f1f1;
        position: relative;
        bottom: 0;
        width: 100%;
    }
</style>

<body>
    <div class="container-fluid pt-3">
        <h1 class="text-center mb-4">Welcome to the Flight Booking System</h1>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header text-white">
                        <h5 class="mb-0">User Dashboard</h5>
                    </div>

                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-header">Total Users</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $user_count; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-header">Booked Flights</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $booked_flights; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-warning mb-3">
                                    <div class="card-header">Available Flights</div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $available_flights; ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>


                        
                    </div>
                    <div class="card mt-4">
                            <div class="card-header text-white" style="background-color: #213555;">
                                <h5 class="mb-0">Upcoming Flight Dates</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($flight_dates)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Day of Week</th>
                                                    <th>Total Flights</th>
                                                    <th>Available Seats</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($flight_dates as $date):
                                                    $flight_details_query = "
                                                        SELECT 
                                                            COUNT(*) as total_flights,
                                                            SUM(seats) as total_seats
                                                        FROM flight_list 
                                                        WHERE DATE(departure_datetime) = '$date'
                                                    ";
                                                    $flight_details_result = $conn->query($flight_details_query);
                                                    $flight_details = $flight_details_result->fetch_assoc();
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($date); ?></td>
                                                        <td><?php echo date('l', strtotime($date)); ?></td>
                                                        <td><?php echo $flight_details['total_flights']; ?></td>
                                                        <td><?php echo $flight_details['total_seats']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No upcoming flights scheduled.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer mt-4">
        <p class="text-center text-muted">&copy; 2024 Flight Booking System. All rights reserved.</p>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='confirm'>Continue</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.js"></script>
</body>