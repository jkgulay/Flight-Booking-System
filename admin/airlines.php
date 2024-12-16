<?php include('db_connect.php'); ?>

<div class="container-fluid pt-3">
    <div class="row">
        <!-- Form Panel -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plane mr-2"></i>Airlines Management
                    </h5>
                </div>
                <form action="" id="manage-airlines" enctype="multipart/form-data" autocomplete="off">
                    <div class="card-body">
                        <input type="hidden" name="id">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fas fa-tag mr-2"></i>Airline Name
                            </label>
                            <input 
                                type="text" 
                                name="airlines" 
                                class="form-control" 
                                placeholder="Enter airline name"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fas fa-image mr-2"></i>Logo
                            </label>
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input" 
                                    name="img" 
                                    id="logoInput" 
                                    accept="image/*"
                                >
                                <label class="custom-file-label" for="logoInput">Choose file</label>
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <img 
                                src="" 
                                alt="Airline Logo" 
                                id="cimg" 
                                class="img-fluid rounded shadow-sm" 
                                style="max-height: 200px; display: none;"
                            >
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save mr-2"></i>Save
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="_reset()">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Panel -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Airlines List
                    </h5>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm" id="refresh-table">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="airlinesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Logo</th>
                                    <th>Airline Name</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                $airlines = $conn->query("SELECT * FROM airlines_list ORDER BY id ASC");
                                while($row = $airlines->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td class="text-center">
                                        <img 
                                            src="../assets/img/<?php echo htmlspecialchars($row['logo_path']); ?>" 
                                            alt="<?php echo htmlspecialchars($row['airlines']); ?>" 
                                            class="img-thumbnail" 
                                            style="max-width: 100px; max-height: 100px;"
                                        >
                                    </td>
                                    <td><?php echo htmlspecialchars($row['airlines']); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button 
                                                class="btn btn-sm btn-warning edit_airline" 
                                                data-id="<?php echo $row['id'] ?>" 
                                                data-airlines="<?php echo htmlspecialchars($row['airlines']) ?>" 
                                                data-logo_path="<?php echo htmlspecialchars($row['logo_path']) ?>"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                class="btn btn-sm btn-danger delete_airline" 
                                                data-id="<?php echo $row['id'] ?>"
                                                title="Delete"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f4f6f9;
    }
    .card {
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .card-header {
        padding: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
		background-color: #213555;
    }
    .table td, .table th {
        vertical-align: middle !important;
        padding: 12px;
    }
</style>

<script>
$(document).ready(function() {
    // DataTable initialization
    $('#airlinesTable').DataTable({
        responsive: true,
        language: {
            searchPlaceholder: "Search airlines...",
            lengthMenu: "Show _MENU_ entries"
        },
        columnDefs: [{
            targets: [-1],
            orderable: false
        }]
    });

    // Custom file input label
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        
        // Display image preview
        displayImg(this);
    });

    // Reset form function
    function _reset() {
        $('#manage-airlines')[0].reset();
        $('[name="id"]').val('');
        $('#cimg').attr('src', '').hide();
        $('.custom-file-label').html('Choose file');
    }

    // Image display function
    function displayImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cimg').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Form submission
    $('#manage-airlines').on('submit', function(e) {
        e.preventDefault();
         start_load();
        $.ajax({
            url: 'ajax.php?action=save_airlines',
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully added", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (resp == 2) {
                    alert_toast("Data successfully updated", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
            },
            error: function() {
                alert_toast("An error occurred while processing your request.", 'error');
            }
        });
        end_load();
    });

    // Edit airline
    $('.edit_airline').click(function() {
        start_load();
        var cat = $('#manage-airlines');
        cat.get(0).reset();
        cat.find("[name='id']").val($(this).data('id'));
        cat.find("[name='airlines']").val($(this).data('airlines'));
        $('#cimg').attr("src", "../assets/img/" + $(this).data('logo_path')).show();
        end_load();
    });

    // Delete airline
    $('.delete_airline').click(function() {
        _conf("Are you sure to delete this airline?", "delete_airline", [$(this).data('id')]);
    });
});

// Delete airline function
function delete_airline(id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=delete_airlines',
        method: 'POST',
        data: { id: id },
        success: function(resp) {
            if (resp == 1) {
                alert_toast("Data successfully deleted", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        },
        error: function() {
            alert_toast("An error occurred while deleting the airline.", 'error');
        }
    });
    end_load();
}
</script>