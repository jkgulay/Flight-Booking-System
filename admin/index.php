<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <title>Admin | Flight Booking System</title>

  <?php
  session_start();
  if (!isset($_SESSION['login_id'])) {
    header('location:login.php');
  }
  include('./header.php');
  include('./db_connect.php');

  $user_id = $_SESSION['user_id']; 
  ?>

  <style>
    body {
      background: #80808045;
    }

    .modal-dialog.large {
      width: 80% !important;
      max-width: unset;
    }

    .modal-dialog.mid-large {
      width: 50% !important;
      max-width: unset;
    }
  </style>
</head>

<body>
  <?php include 'topbar.php' ?>
  <?php include 'navbar.php' ?>

  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="mr-auto">Notification</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Error
    </div>
  </div>

  <main id="view-panel">
    <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
    <?php include $page . '.php' ?>
  </main>

  <div id="preloader" style="display: none;"></div>
  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

  <div class="modal fade" id="confirm_modal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="delete_content"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="uni_modal" tabindex="-1" aria-labelledby="uniModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uniModalLabel"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.start_load = function() {
      $('body').prepend('<div id="preloader2"></div>');
    };

    window.end_load = function() {
      $('#preloader2').fadeOut('fast', function() {
        $(this).remove();
      });
    };

    window.uni_modal = function(title = '', url = '', size = "") {
      start_load();
      $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        error: function(err) {
          console.error(err);
          alert("An error occurred");
          end_load();
        },
        success: function(resp) {
          if (resp.status === 'success') {
            $('#uni_modal .modal-title').html(title);
            $('#uni_modal .modal-body').html(resp.data);
            if (size) {
              $('#uni_modal .modal-dialog').addClass(size);
            } else {
              $('#uni_modal .modal-dialog').removeClass().addClass("modal-dialog modal-md");
            }
            $('#uni_modal').modal('show');
          } else {
            alert(resp.message);
          }
          end_load();
        }
      });
    };

    window._conf = function(msg = '', func = '', params = []) {
      $('#confirm_modal #confirm').attr('onclick', `${func}(${params.join(',')})`);
      $('#confirm_modal .modal-body').html(msg);
      $('#confirm_modal').modal('show');
    };

    window.alert_toast = function(msg = 'TEST', bg = 'success') {
      $('#alert_toast').removeClass('bg-success bg-danger bg-info bg-warning');
      $('#alert_toast').addClass(`bg-${bg}`);
      $('#alert_toast .toast-body').html(msg);
      $('#alert_toast').toast({
        delay: 3000
      }).toast('show');
    };

    $(document).ready(function() {
      $('#preloader').fadeOut('fast', function() {
        $(this).remove();
      });

      $('.datetimepicker').datetimepicker({
        format: 'Y/m/d H:i',
        startDate: '+3d'
      });

      $('.select2').select2({
        placeholder: "Please select here",
        width: "100%"
      });
    });
  </script>
</body>

</html>