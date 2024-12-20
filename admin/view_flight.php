<?php
include 'db_connect.php';

function get_flight_price($flight_id)
{
    global $conn;
    $query = "SELECT price FROM flight_list WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $price = $result->fetch_assoc();
    return $price ? $price['price'] : null;
}

// Fetch airport names
$airport = $conn->query("SELECT * FROM airport_list");
$aname = [];
while ($row = $airport->fetch_assoc()) {
    $aname[$row['id']] = ucwords($row['airport'] . ', ' . $row['location']);
}

// Fetch flight details
$flights_query = "SELECT f.*, a.airlines, a.logo_path 
                  FROM flight_list f 
                  INNER JOIN airlines_list a ON f.airline_id = a.id 
                  ORDER BY f.id DESC";
$qry = $conn->query($flights_query);

$flights = [];
if ($qry) {
    while ($row = $qry->fetch_assoc()) {
        $flights[] = $row;
    }
} else {
    echo "Error fetching flights: " . $conn->error;
}

// Fetch booked flights
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

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #213555;">
                <h4 class="card-title mb-0"><b>Flight List</b></h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover" id="flight-list">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Information</th>
                            <th class="text-center">Seats</th>
                            <th class="text-center">Booked</th>
                            <th class="text-center">Available</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $row):
                            $booked = $conn->query("SELECT COUNT(*) AS total FROM booked_flight WHERE flight_id = " . $row['id'])->fetch_assoc()['total'];
                            $available = max(0, $row['seats'] - $booked);
                            $price = get_flight_price($row['id']);
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['date_created'])); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/img/<?php echo htmlspecialchars($row['logo_path']); ?>" alt="Airline Logo" class="img-fluid rounded-circle" style="width: 50px; height: auto;">
                                        <div class="ml-3">
                                            <p class="mb-1">Airline: <strong><?php echo htmlspecialchars($row['airlines']); ?></strong></p>
                                            <p class="mb-1">From: <strong><?php echo $aname[$row['departure_airport_id']] ?? "Unknown Airport"; ?></strong></p>
                                            <p class="mb-1">To: <strong><?php echo $aname[$row['arrival_airport_id']] ?? "Unknown Airport"; ?></strong></p>
                                            <p class="mb-1">Departure: <strong><?php echo date('M d, Y h:i A', strtotime($row['departure_datetime'])); ?></strong></p>
                                            <p class="mb-0">Arrival: <strong><?php echo date('M d, Y h:i A', strtotime($row['arrival_datetime'])); ?></strong></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right"><?php echo number_format($row['seats']); ?></td>
                                <td class="text-right"><?php echo $booked; ?></td>
                                <td class="text-right"><?php echo $available; ?></td>
                                <td class="text-right"><?php echo number_format($price, 2); ?></td>
                                <td class="text-center">
                                    <a href="book_flight.php?flight_id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-plane"></i> Book Now
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manageFlightModal" tabindex="-1" aria-labelledby="manageFlightModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="manage-flight">
                <input type="hidden" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageFlightModalLabel">Flight Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Add fields for flight details here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#flight-list').DataTable();

        $('#new_flight').on('click', function() {
            $('#manage-flight')[0].reset();
            $('#manage-flight input[name="id"]').val('');
            $('#manageFlightModal').modal('show');
        });
    });
</script>