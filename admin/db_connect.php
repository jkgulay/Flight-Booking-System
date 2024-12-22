<?php 

$host = 'localhost';
$dbname = 'flight_booking_db'; 
$username = 'postgres'; 
$password = 'admin'; 

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
    ]);
    echo "";
} catch (PDOException $e) {
    die("Could not connect to PostgreSQL: " . $e->getMessage());
}
?>