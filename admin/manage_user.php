<?php 
include('db_connect.php');

// Secure the page and prevent SQL injection
$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$meta = null;

if ($user_id) {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $meta = $result->fetch_assoc();
    }
}
?>

<div class="container-fluid">
    <form action="" id="manage-user">
        <input type="hidden" name="id" id="user_id" value="<?php echo isset($meta['id']) ? htmlspecialchars($meta['id']) : '' ?>">
        
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" 
                   value="<?php echo isset($meta['name']) ? htmlspecialchars($meta['name']) : '' ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   value="<?php echo isset($meta['username']) ? htmlspecialchars($meta['username']) : '' ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="password">
                <?php echo $user_id ? 'New Password (leave blank to keep current)' : 'Password'; ?>
            </label>
            <input type="password" name="password" id="password" class="form-control" 
                   <?php echo $user_id ? '' : 'required' ?>>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" name="contact" id="contact" class="form-control" 
                   value="<?php echo isset($meta['contact']) ? htmlspecialchars($meta['contact']) : '' ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control" 
                   value="<?php echo isset($meta['address']) ? htmlspecialchars($meta['address']) : '' ?>">
        </div>
        
        <div class="form-group">
            <label for="type">User Type</label>
            <select name="type" id="type" class="custom-select" required>
                <option value="">Select User Type</option>
                <option value="1" <?php echo (isset($meta['type']) && $meta['type'] == 1) ? 'selected' : '' ?>>Admin</option>
                <option value="2" <?php echo (isset($meta['type']) && $meta['type'] == 2) ? 'selected' : '' ?>>Staff</option>
                <option value="3" <?php echo (isset($meta['type']) && $meta['type'] == 3) ? 'selected' : '' ?>>Customer</option>
            </select>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#manage-user').on('submit', function(e) {
        e.preventDefault();
        
        // Client-side validation
        const name = $('#name').val().trim();
        const username = $('#username').val().trim();
        const type = $('#type').val();
        
        if (!name || !username || !type) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields'
            });
            return;
        }
        
        // Start loading indicator
        start_load();
        
        // Prepare form data
        const formData = $(this).serialize();
        
        // AJAX submission
        $.ajax({
            url: 'ajax.php?action=save_user',
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                // Stop loading
                end_load();
                
                // Handle response
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => {
                        // Close modal and reload page or update table
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
    });
});
</script>