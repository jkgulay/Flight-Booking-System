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

$airport = $conn->query("SELECT * FROM airport_list");
$aname = [];
while ($row = $airport->fetch_assoc()) {
    $aname[$row['id']] = ucwords($row['airport'] . ', ' . $row['location']);
}

$qry = $conn->query("SELECT f.*, a.airlines, a.logo_path 
                    FROM flight_list f 
                    INNER JOIN airlines_list a ON f.airline_id = a.id 
                    ORDER BY f.id DESC");
?>

<div class="container-fluid pt-3">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #213555;">
                <h4 class="card-title mb-0"><b>Flight List</b></h4>
                <button class="btn btn-light" id="new_flight">
                    <i class="fa fa-plus"></i> Add New Flight
                </button>
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
                        <?php while ($row = $qry->fetch_assoc()):
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
                                    <button class="btn btn-outline-danger btn-sm delete_flight" data-id="<?php echo $row['id']; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
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
                    <div class="form-group">
                        <label for="airline">Airline</label>
                        <select name="airline" id="airline" class="form-control select2" required>
                            <option value="">Select Airline</option>
                            <?php
                            $airline = $conn->query("SELECT * FROM airlines_list ORDER BY airlines ASC");
                            while ($row = $airline->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['airlines']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plane_no">Plane No</label>
                        <input type="text" name="plane_no" id="plane_no" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="departure_airport_id">Departure Airport</label>
                        <select name="departure_airport_id" id="departure_airport_id" class="form-control select2" required>
                            <option value="">Select Departure Airport</option>
                            <?php foreach ($aname as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="arrival_airport_id">Arrival Airport</label>
                        <select name="arrival_airport_id" id="arrival_airport_id" class="form-control select2" required>
                            <option value="">Select Arrival Airport</option>
                            <?php foreach ($aname as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="departure_datetime">Departure Date/Time</label>
                        <input type="datetime-local" name="departure_datetime" id="departure_datetime" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="arrival_datetime">Arrival Date/Time</label>
                        <input type="datetime-local" name="arrival_datetime" id="arrival_datetime" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="seats">Seats</label>
                        <input type="number" name="seats" id="seats" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" name="price" id="price" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
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

        $('#manage-flight').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'ajax.php?action=save_flight',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert_toast('Flight successfully saved.', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert_toast('Failed to save flight. Try again.', 'danger');
                    }
                },
            });
            location.reload();

        });

        $('.delete_flight').on('click', function() {
            const id = $(this).data('id');
            _conf('Are you sure you want to delete this flight?', 'delete_flight', [id]);
        });

        window.delete_flight = function(id) {
            $.ajax({
                url: 'ajax.php?action=delete_flight',
                method: 'POST',
                data: {
                    id
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert_toast('Flight successfully deleted.', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert_toast('Failed to delete flight. Try again.', 'danger');
                    }
                },
            });
            location.reload();
        };
    });
</script>