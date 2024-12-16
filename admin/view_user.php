<?php
include 'db_connect.php';

// Enhanced error handling and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate and sanitize input
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'No user ID provided']));
}

$id = intval($_GET['id']);

// Prepare comprehensive user query
$stmt = $conn->prepare("
    SELECT 
        u.*,
        CASE 
            WHEN u.type = 1 THEN 'Admin'
            WHEN u.type = 2 THEN 'Staff'
            WHEN u.type = 3 THEN 'Customer'
            ELSE 'Unknown'
        END AS user_role
    FROM users u 
    WHERE u.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(['error' => 'User not found']));
}

$user = $result->fetch_assoc();

// Fetch recent bookings (modify this query based on your actual database schema)
$bookings_stmt = $conn->prepare("
    SELECT 
        bf.id, 
        f.plane_no, 
        al.airlines, 
        dep.airport as departure_airport, 
        arr.airport as arrival_airport, 
        f.departure_datetime, 
        f.arrival_datetime
    FROM booked_flight bf
    JOIN flight_list f ON bf.flight_id = f.id
    JOIN airlines_list al ON f.airline_id = al.id
    JOIN airport_list dep ON f.departure_airport_id = dep.id
    JOIN airport_list arr ON f.arrival_airport_id = arr.id
    WHERE bf.name = ? OR bf.contact = ?
    ORDER BY f.departure_datetime DESC
    LIMIT 5
");
$bookings_stmt->bind_param("ss", $user['name'], $user['contact']);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$recent_bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);

$total_bookings_stmt = $conn->prepare("
    SELECT COUNT(*) as booking_count
    FROM booked_flight
    WHERE name = ? OR contact = ?
");
$total_bookings_stmt->bind_param("ss", $user['name'], $user['contact']);
$total_bookings_stmt->execute();
$total_bookings_result = $total_bookings_stmt->get_result();
$total_bookings = $total_bookings_result->fetch_assoc()['booking_count'];
?>

<div class="modal-body user-profile-modal">
    <div class="container-fluid">
        <div class="row">
            <!-- Profile Header -->
            <div class="col-12 user-profile-header text-center mb-4">
                <div class="avatar-container">
                    <div class="avatar bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                </div>
                <h2 class="mt-3 mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="text-muted">
                    <span class="badge 
                    <?php
                    switch ($user['user_role']) {
                        case 'Admin':
                            echo 'badge-danger';
                            break;
                        case 'Staff':
                            echo 'badge-warning';
                            break;
                        case 'Customer':
                            echo 'badge-success';
                            break;
                        default:
                            echo 'badge-secondary';
                    }
                    ?>">
                        <?php echo htmlspecialchars($user['user_role']); ?>
                    </span>
                </p>
            </div>

            <!-- User Details -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-user mr-2"></i>
                        <h5 class="card-title mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th class="w-40"><i class="fas fa-envelope mr-2"></i>Username</th>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-phone mr-2"></i>Contact</th>
                                <td><?php echo htmlspecialchars($user['contact'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-map-marker-alt mr-2"></i>Address</th>
                                <td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Account Stats -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex align-items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        <h5 class="card-title mb-0">Account Statistics</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong><i class="fas fa-calendar-alt mr-2"></i>Account Created:</strong>
                                <?php
                                echo isset($user['created_at'])
                                    ? date('F j, Y, g:i a', strtotime($user['created_at']))
                                    : 'N/A';
                                ?>
                            </li>
                            <li>
                                <strong><i class="fas fa-plane mr-2"></i>Total Bookings:</strong>
                                <?php echo $user['total_bookings'] ?? 0; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-history mr-2"></i>
                        <h5 class="card-title mb-0">Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_bookings)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Airline</th>
                                            <th>Flight</th>
                                            <th>Departure</th>
                                            <th>Arrival</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['airlines']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['plane_no']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['departure_airport']); ?><br>
                                                    <small><?php echo date('M j, Y H:i', strtotime($booking['departure_datetime'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['arrival_airport']); ?><br>
                                                    <small><?php echo date('M j, Y H:i', strtotime($booking['arrival_datetime'])); ?></small>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No recent bookings found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        // Function to view booking details
        $('.view-booking').on('click', function() {
            var bookingId = $(this).data('id');
            // Implement AJAX call to fetch booking details
            $.ajax({
                url: 'get_booking_details.php',
                method: 'GET',
                data: {
                    id: bookingId
                },
                success: function(data) {
                    // Handle success response
                    // Display booking details in a modal or alert
                    alert('Booking details: ' + JSON.stringify(data));
                },
                error: function() {
                    alert('Error fetching booking details.');
                }
            });
        });
    });
</script>

<style>
    .user-profile-modal {
        font-family: Arial, sans-serif;
    }

    .user-profile-header {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
    }

    .avatar-container {
        margin-bottom: 15px;
    }

    .avatar {
        width: 100px;
        height: 100px;
        font-size: 40px;
        line-height: 100px;
        border-radius: 50%;
    }

    .card {
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }

    .card-header {
        font-weight: bold;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>