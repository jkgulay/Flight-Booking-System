<?php
include 'db_connect.php';

// Function to execute a query and return the result
function executeQuery($conn, $query, $params = [])
{
    $stmt = $conn->prepare($query);
    if ($params) {
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }
    }
    $stmt->execute();
    return $stmt;
}

// Get total users
$user_count_query = "SELECT COUNT(*) AS total_users FROM users";
$user_count_result = executeQuery($conn, $user_count_query);
$user_count = $user_count_result->fetch(PDO::FETCH_ASSOC)['total_users'];

// booked_flight_summary
$booked_flights_query = "SELECT COUNT(*) AS total_booked FROM booked_flight_summary";
$booked_flights_result = executeQuery($conn, $booked_flights_query);
$booked_flights = $booked_flights_result->fetch(PDO::FETCH_ASSOC)['total_booked'];

// Get total available flights
$available_flights_query = "SELECT COUNT(*) AS total_available FROM flight_list WHERE seats > 0";
$available_flights_result = executeQuery($conn, $available_flights_query);
$available_flights = $available_flights_result->fetch(PDO::FETCH_ASSOC)['total_available'];

// Get upcoming flight dates
$flight_dates_query = "SELECT DISTINCT DATE(departure_datetime) AS flight_date FROM flight_list";
$flight_dates_result = executeQuery($conn, $flight_dates_query);
$flight_dates = $flight_dates_result->fetchAll(PDO::FETCH_COLUMN);

// Get booking count for a specific flight - tablue-valued function
$flight_id = 1; // Example flight ID
$booking_count_query = "SELECT get_booking_count_by_flight(:flight_id) AS total_bookings";
$booking_count_result = executeQuery($conn, $booking_count_query, [':flight_id' => $flight_id]);
$total_bookings = $booking_count_result->fetch(PDO::FETCH_ASSOC)['total_bookings'];

// Get activity logs
$logs_query = "SELECT 
                logs.timestamp, 
                logs.action_type, 
                logs.table_name, 
                logs.affected_columns, 
                logs.details, 
                users.name AS user_name 
               FROM logs 
               INNER JOIN users ON logs.user_id = users.id 
               ORDER BY logs.timestamp DESC";
$logs_result = executeQuery($conn, $logs_query);
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

    table thead {
        background-color: #213555;
        color: white;
    }

    table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<body>
    <div class="container-fluid pt-3">
        <h1 class="text-center mb-4">Welcome to the Flight Booking System</h1>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Total Users:</strong> <?php echo htmlspecialchars($user_count); ?></p>
                        <p><strong>Total Flights Booked:</strong> <?php echo htmlspecialchars($booked_flights); ?></p>
                        <p><strong>Total Available Flights:</strong> <?php echo htmlspecialchars($available_flights); ?></p>
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
                                            <th>Booked Seats</th>
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
                                                WHERE DATE(departure_datetime) = :date
                                            ";
                                            $flight_details_result = executeQuery($conn, $flight_details_query, [':date' => $date]);
                                            $flight_details = $flight_details_result->fetch(PDO::FETCH_ASSOC);

                                            $booking_count_query = "
                                                SELECT SUM(get_booking_count_by_flight(id)) AS total_bookings
                                                FROM flight_list
                                                WHERE DATE(departure_datetime) = :date
                                            ";
                                            $booking_count_result = executeQuery($conn, $booking_count_query, [':date' => $date]);
                                            $total_bookings = $booking_count_result->fetch(PDO::FETCH_ASSOC)['total_bookings'] ?? 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($date); ?></td>
                                                <td><?php echo date('l', strtotime($date)); ?></td>
                                                <td><?php echo htmlspecialchars($flight_details['total_flights']); ?></td>
                                                <td><?php echo htmlspecialchars($flight_details['total_seats']); ?></td>
                                                <td><?php echo htmlspecialchars($total_bookings); ?></td>
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

                <div class="card mt-4">
                    <div class="card-header text-white" style="background-color: #213555;">
                        <h5 class="mb-0">Activity Logs</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($logs_result->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Table</th>
                                            <th>Affected Columns</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($log = $logs_result->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                                <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars($log['action_type']); ?></td>
                                                <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                                                <td><?php echo htmlspecialchars($log['affected_columns']); ?></td>
                                                <td><?php echo htmlspecialchars($log['details']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No activity logs found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer mt-4">
        <p class="text-center text-muted">&copy; 2024 Flight Booking System. All rights reserved.</p>
    </div>

</body>