<?php
include 'db_connect.php';

// Function to get user count
function getUserCount()
{
    global $conn;

    $query = "SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN type = 1 THEN 1 ELSE 0 END) as admin_users,
        SUM(CASE WHEN type = 2 THEN 1 ELSE 0 END) as staff_users,
        SUM(CASE WHEN type = 3 THEN 1 ELSE 0 END) as customer_users
    FROM users";

    $result = $conn->query($query);
    return $result->fetch_assoc();
}

// Get user statistics
$userStats = getUserCount();

// Fetch users with more details
$users = $conn->query("
    SELECT 
        u.*, 
        CASE 
            WHEN u.type = 1 THEN 'Admin'
            WHEN u.type = 2 THEN 'Staff'
            WHEN u.type = 3 THEN 'Customer'
            ELSE 'Unknown'
        END AS user_role
    FROM users u 
    ORDER BY u.name ASC
");
?>

<div class="container-fluid pt-3">
    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $userStats['total_users']; ?></h4>
                            <span>Total Users</span>
                        </div>
                        <i class="fa fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $userStats['admin_users']; ?></h4>
                            <span>Admin Users</span>
                        </div>
                        <i class="fa fa-user-shield fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo $userStats['staff_users'] + $userStats['customer_users']; ?></h4>
                            <span>Staff & Customer Users</span>
                        </div>
                        <i class="fa fa-users-cog fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12 d-flex justify-content-between align-items-center">
            <h2 class="" style="color:#213555;">User Management</h2>
            <div>
                <button class="btn btn-success btn-sm mr-2" id="export_users">
                    <i class="fa fa-file-excel mr-2"></i>Export Users
                </button>
                <button class="btn btn-primary btn-sm" id="new_user">
                    <i class="fa fa-plus-circle mr-2"></i>Add New User
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="userTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Contact</th>
                            <th>User Role</th>
                            <th>Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = $users->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                  
                                        <?php echo htmlspecialchars($row['name']) ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['username']) ?></td>
                                <td><?php echo htmlspecialchars($row['contact']) ?></td>
                                <td>
                                    <span class="badge 
                                    <?php
                                    switch ($row['user_role']) {
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
                                    ?>
                                ">
                                        <?php echo htmlspecialchars($row['user_role']) ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['address']) ?></td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item edit_user" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                                <i class="fa fa-edit mr-2 text-warning"></i>Edit
                                            </a>
                                            <a class="dropdown-item view_user" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                                <i class="fa fa-eye mr-2 text-info"></i>View Details
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item delete_user" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                                <i class="fa fa-trash mr-2 text-danger"></i>Delete
                                            </a>
                                        </div>
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

<script>
(function($) {
    // Ensure jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // DataTable initialization
    function initializeDataTable() {
        return $('#userTable').DataTable({
            responsive: true,
            processing: true,
            language: {
                searchPlaceholder: "Search users...",
                lengthMenu: "Show _MENU_ entries",
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
            },
            columnDefs: [{
                targets: [-1],
                orderable: false
            }]
        });
    }

    // Export users functionality
    function setupExportUsers() {
        $('#export_users').on('click', function() {
            window.location.href = '?action=export_users';
        });
    }

    function setupUserManagement() {
        // Add new user modal
        $('#new_user').on('click', function() {
            uni_modal('Add New User', 'manage_user.php', 'modal-lg');
        });

        // Edit user modal
        $(document).on('click', '.edit_user', function() {
            uni_modal('Edit User', 'manage_user.php?id=' + $(this).data('id'), 'modal-lg');
        });

        // View user details modal
        $(document).on('click', '.view_user', function() {
            uni_modal('User  Details', 'view_user.php?id=' + $(this).data('id'), 'modal-lg');
        });

        // Delete user confirmation
        $(document).on('click', '.delete_user', function() {
            const userId = $(this).data('id');
            _conf("Are you sure you want to delete this user?", deleteUser , [userId]);
        });
    }

    // Delete user function
    function deleteUser (id) {
        // Start loading
        start_load();

        $.ajax({
            url: 'ajax.php?action=delete_user',
            method: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(response) {
                // Stop loading
                end_load();

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message
                    }).then(() => {
                        // Reload the page or remove the row
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                // Stop loading
                end_load();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong: ' + error
                });
            }
        });
    }

    // Save user function
    function saveUser () {
        // Start loading
        start_load();

        // Serialize form data
        const formData = $('#manage-user').serialize();

        $.ajax({
            url: 'ajax.php?action=save_user',
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                // Stop loading
                end_load();

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => {
                        // Close modal and reload page
                        $('#uni_modal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                // Stop loading
                end_load();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred: ' + error
                });
            }
        });
    }

    // Loading indicator start
    function start_load() {
        $('body').prepend('<div class="loader-container"><div class="loader"></div></div>');
    }

    // Loading indicator end
    function end_load() {
        $('.loader-container').remove();
    }

    // Confirmation dialog
    function _conf(msg, func, params = []) {
        Swal.fire({
            title: 'Are you sure?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Call the function with provided parameters
                func(...params);
            }
        });
    }

    // Modal function
    function uni_modal(title, url, size = 'modal-lg') {
        $('#uni_modal .modal-title').text(title);
        $('#uni_modal .modal-body').load(url);
        $('#uni_modal').modal('show');
    }

    $(document).ready(function() {
        // Initialize components
        const userTable = initializeDataTable();
        setupExportUsers();
        setupUserManagement();

        // Form submission handler
        $(document).on('submit', '#manage-user', function(e) {
            e.preventDefault();
            saveUser ();
        });

        // Expose functions globally if needed
        window.deleteUser  = deleteUser ;
        window.saveUser  = saveUser ;
    });

})(jQuery);
</script>

<style>
.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>