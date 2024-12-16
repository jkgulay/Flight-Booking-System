<?php include 'db_connect.php'; ?>

<div class="container-fluid pt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <large class="card-title">
                    <b>Flight List</b>
                </large>
                <button class="btn btn-primary btn-block col-md-2 float-right" type="button" id="new_flight">
                    <i class="fa fa-plus"></i> New Flight
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="flight-list">
                    <colgroup>
                        <col width="10%">
                        <col width="35%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center">Date</th>
                            <th class="text-center">Information</th>
                            <th class="text-center">Seats</th>
                            <th class="text-center">Booked</th>
                            <th class="text-center">Available</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch airports into an array
                        $airport = $conn->query("SELECT * FROM airport_list");
                        $aname = [];
                        while ($row = $airport->fetch_assoc()) {
                            $aname[$row['id']] = ucwords($row['airport'] . ', ' . $row['location']);
                        }

                        // Fetch flights and associated data
                        $qry = $conn->query("SELECT f.*, a.airlines, a.logo_path 
                                            FROM flight_list f 
                                            INNER JOIN airlines_list a ON f.airline_id = a.id 
                                            ORDER BY f.id DESC");
                        while ($row = $qry->fetch_assoc()):
                            $booked = $conn->query("SELECT * FROM booked_flight WHERE flight_id = " . $row['id'])->num_rows;
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['date_created'])) ?></td>
                                <td>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <img src="../assets/img/<?php echo htmlspecialchars($row['logo_path']) ?>" alt="Airline Logo" class="btn-rounder badge-pill">
                                        </div>
                                        <div class="col-sm-6">
                                            <p>Airline: <b><?php echo htmlspecialchars($row['airlines']) ?></b></p>
                                            <p>
                                                Location: <b>
                                                    <?php 
                                                    echo isset($aname[$row['departure_airport_id']]) ? $aname[$row['departure_airport_id']] : "Unknown Airport";
                                                    echo " - ";
                                                    echo isset($aname[$row['arrival_airport_id']]) ? $aname[$row['arrival_airport_id']] : "Unknown Airport";
                                                    ?>
                                                </b>
                                            </p>
                                            <p>Departure: <b><?php echo date('M d, Y h:i A', strtotime($row['departure_datetime'])) ?></b></p>
                                            <p>Arrival: <b><?php echo date('M d, Y h:i A', strtotime($row['arrival_datetime'])) ?></b></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right"><?php echo number_format($row['seats']) ?></td>
                                <td class="text-right"><?php echo $booked ?></td>
                                <td class="text-right"><?php echo max(0, $row['seats'] - $booked) ?></td>
                                <td class="text-right"><?php echo number_format($row['price'], 2) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm edit_flight" type="button" data-id="<?php echo $row['id'] ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm delete_flight" type="button" data-id="<?php echo $row['id'] ?>">
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

<style>
    td p {
        margin: unset;
    }
    td img {
        width: 8vw;
        height: 12vh;
    }
    td {
        vertical-align: middle !important;
    }
</style>

<script>
    $(document).ready(function() {
        $('#flight-list').DataTable();

        $('#new_flight').click(function () {
            uni_modal("New Flight", "manage_flight.php", 'mid-large');
        });

        $('.edit_flight').click(function () {
            uni_modal("Edit Flight", "manage_flight.php?id=" + $(this).attr('data-id'), 'mid-large');
        });

        $('.delete_flight').click(function () {
            _conf("Are you sure you want to delete this flight?", "delete_flight", [$(this).attr('data-id')]);
        });
    });

    function delete_flight(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_flight',
            method: 'POST',
            data: { id: id },
            success: function (response) {
                if (response == 1) {
                    alert_toast("Flight successfully deleted", 'success');
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("Failed to delete flight. Try again.", 'danger');
                }
            },
            error: function() {
                alert_toast("Error deleting flight.", 'danger');
            }
        });
    }
</script>
