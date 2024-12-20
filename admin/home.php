<?php
include 'db_connect.php';
$user_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null;
if ($user_id === null) {
    header("Location: login.php");
    exit();
}

//flight_details view
$flights_query = "SELECT flight_id, airlines, departure_airport, arrival_airport, departure_datetime, arrival_datetime, price, seats FROM flight_details WHERE seats > 0 ORDER BY departure_datetime ASC";
$flights_result = $conn->query($flights_query);
$flights = [];
if ($flights_result) {
    while ($row = $flights_result->fetch_assoc()) {
        $flights[] = $row;
    }
} else {
    echo "Error fetching flights: " . $conn->error;
}

$booked_flights_query = "
    SELECT b.id AS booking_id, 
           f.flight_id, 
           f.airlines, 
           f.departure_airport, 
           f.arrival_airport, 
           f.departure_datetime, 
           f.arrival_datetime, 
           f.price, 
           b.status 
    FROM booked_flight b
    INNER JOIN flight_details f ON b.flight_id = f.flight_id
    WHERE b.user_id = " . intval($user_id) . "  -- Ensure user_id is an integer
    ORDER BY b.id DESC
";

//booked_flight_summary view
$booked_flights_result = $conn->query($booked_flights_query);
$booked_flights = [];
if ($booked_flights_result) {
    while ($row = $booked_flights_result->fetch_assoc()) {
        $booked_flights[] = $row;
    }
} else {
    echo "Error fetching booked flights: " . $conn->error;
}
?>

<style>
    body {
        background-color: #f8f9fa;
        margin: 0;
        font-family: Arial, sans-serif;
    }

    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #213555;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 10px;
    }

    .welcome-message {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #3E5879;
        color: white;
    }

    .table tr:hover {
        background-color: #f1f1f1;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 0.9rem;
        color: #6c757d;
        padding: 10px 0;
        background-color: #f1f1f1;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
</style>

<div class="container">
    <div class="welcome-section text-center">
        <h1 class="welcome-message">Welcome to the Flight Booking System</h1>
        <p>Your gateway to seamless flight bookings and travel experiences.</p>
    </div>
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Booked Flights</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($booked_flights)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Flight Number</th>
                                        <th>Airlines</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Departure</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($booked_flights as $flight): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($flight['flight_id']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['airlines']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['departure_airport']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['arrival_airport']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($flight['departure_datetime'])); ?></td>
                                            <td><?php echo ucfirst($flight['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            You have not booked any flights yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Flights</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($flights)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Flight Number</th>
                                        <th>Airlines</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Available Seats</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($flights as $flight): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($flight['flight_id']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['airlines']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['departure_airport']); ?></td>
                                            <td><?php echo htmlspecialchars($flight['arrival_airport']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($flight['departure_datetime'])); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($flight['arrival_datetime'])); ?></td>
                                            <td><?php echo $flight['seats']; ?></td>
                                            <td><?php echo number_format($flight['price'], 2); ?></td>
                                            <td>
                                                <a href="book_flight.php?flight_id=<?php echo $flight['flight_id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-plane"></i> Book Now
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No flights available at the moment. Please check back later.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <p>&copy; 2023 Flight Booking System. All rights reserved.</p>
</div>