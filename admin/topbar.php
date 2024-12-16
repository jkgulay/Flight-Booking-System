<style>
  .logo {
    margin: 0; 
    padding: 0; 
    display: flex; 
    align-items: center; 
  }

  .navbar {
    padding: 0.5rem 1rem; 
    background-color: #213555; 
  }

  .navbar .navbar-brand {
    font-size: 1.5rem; 
    font-weight: bold; 
    color: #D8C4B6; 
}

  .logout-link {
    color: white; 
    text-decoration: none; 
  }

  .logout-link:hover {
    text-decoration: underline; 
  }
</style>

<nav class="navbar navbar-light fixed-top">
  <div class="container-fluid">
    <div class="d-flex align-items-center">
      <div class="logo me-2">
        <img src="assets/img/bgg.png" alt="logo" class="img-fluid" style="max-width: 50px; height: auto;">
      </div>
      <div class="navbar-brand">Flight Booking System</div>
    </div>
    <div class="ml-auto">
      <a href="ajax.php?action=logout" class="logout-link">
        <?php echo htmlspecialchars($_SESSION['login_name']); ?> <i class=" fa fa-power-off"></i>
      </a>
    </div>
  </div>
</nav>