<?php
include('db_connect.php');

function get_flight_price($flight_id)
{
    global $conn;
    // Call the scalar function in the SQL query
    $query = "SELECT public.get_flight_price(:flight_id) AS price";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':flight_id', $flight_id, PDO::PARAM_INT);
    $stmt->execute();
    $price = $stmt->fetch(PDO::FETCH_ASSOC);
    return $price ? $price['price'] : null;
}

$airport = $conn->query("SELECT * FROM airport_list");
$aname = [];
while ($row = $airport->fetch(PDO::FETCH_ASSOC)) {
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
                        <?php while ($row = $qry->fetch(PDO::FETCH_ASSOC)): // Get booking count for a specific flight - tablue-valued function
                            $booked = $conn->query("SELECT get_booking_count_by_flight(" . $row['id'] . ") AS total")->fetch(PDO::FETCH_ASSOC)['total'];
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
                            while ($row = $airline->fetch(PDO::FETCH_ASSOC)):
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

            // Validate form before submission
            if (!validateFlightForm()) return;

            $.ajax({
                url: 'ajax.php?action=save_flight',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json', // Expect JSON response
                success: function(data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#manageFlightModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save flight',
                            timer: 3000,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.error("Response Text:", xhr.responseText);

                    // Try to parse the response text in case of error
                    try {
                        const data = JSON.parse(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save flight',
                            timer: 3000,
                            showConfirmButton: true
                        });
                    } catch (parseError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred while saving flight details',
                            timer: 3000,
                            showConfirmButton: true
                        });
                    }
                }
            });
        });

        // Edit flight functionality
        $('.edit_flight').on('click', function() {
            // Reset form
            $('#manage-flight')[0].reset();

            // Populate form with existing data
            var id = $(this).data('id');
            var airline = $(this).data('airline_id');
            var planeNo = $(this).data('plane_no');
            var departureAirport = $(this).data('departure_airport_id');
            var arrivalAirport = $(this).data('arrival_airport_id');
            var departureDatetime = $(this).data('departure_datetime');
            var arrivalDatetime = $(this).data('arrival_datetime');
            var seats = $(this).data('seats');
            var price = $(this).data('price');

            // Set form values
            $('#manage-flight input[name="id"]').val(id);
            $('#manage-flight select[name="airline"]').val(airline);
            $('#manage-flight input[name="plane_no"]').val(planeNo);
            $('#manage-flight select[name="departure_airport_id"]').val(departureAirport);
            $('#manage-flight select[name="arrival_airport_id"]').val(arrivalAirport);
            $('#manage-flight input[name="departure_datetime"]').val(departureDatetime);
            $('#manage-flight input[name="arrival_datetime"]').val(arrivalDatetime);
            $('#manage-flight input[name="seats"]').val(seats);
            $('#manage-flight input[name="price"]').val(price);

            // Show modal
            $('#manageFlightModal').modal('show');
        });

        // Delete flight functionality
        $('.delete_flight').on('click', function() {
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
                        url: 'ajax.php?action=delete_flight',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message,
                                    timer: 3000,
                                    showConfirmButton: true
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while deleting the flight',
                                timer: 3000,
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        });
    });

    // Validation function
    function validateFlightForm() {
        const airline = $('[name="airline"]').val();
        const planeNo = $('[name="plane_no"]').val();
        const departureAirport = $('[name="departure_airport_id"]').val();
        const arrivalAirport = $('[name="arrival_airport_id"]').val();
        const departureDatetime = $('[name="departure_datetime"]').val();
        const arrivalDatetime = $('[name="arrival_datetime"]').val();
        const seats = $('[name="seats"]').val();
        const price = $('[name="price"]').val();

        // Validation checks
        const validations = [{
                condition: !airline,
                message: 'Please select an airline'
            },
            {
                condition: !planeNo,
                message: 'Please enter plane number'
            },
            {
                condition: !departureAirport,
                message: 'Please select departure airport'
            },
            {
                condition: !arrivalAirport,
                message: 'Please select arrival airport'
            },
            {
                condition: departureAirport === arrivalAirport,
                message: 'Departure and arrival airports cannot be the same'
            },
            {
                condition: !departureDatetime,
                message: 'Please select departure date and time'
            },
            {
                condition: !arrivalDatetime,
                message: 'Please select arrival date and time'
            },
            {
                condition: !seats || seats <= 0,
                message: 'Please enter a valid number of seats'
            },
            {
                condition: !price || price <= 0,
                message: 'Please enter a valid price'
            }
        ];

        // Check validations
        for (let validation of validations) {
            if (validation.condition) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: validation.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                return false;
            }
        }

        // Additional datetime validation
        const departureTime = new Date(departureDatetime);
        const arrivalTime = new Date(arrivalDatetime);

        if (departureTime >= arrivalTime) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Arrival time must be after departure time',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }

        return true;
    }

    // Delete Flight
    $('.delete_flight').on('click', function() {
        const id = $(this).data('id');

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
                    url: 'ajax.php?action=delete_flight',
                    method: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json', // Expect JSON response
                    success: function(data) {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                timer: 3000,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while deleting the flight',
                            timer: 3000,
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });
</script>