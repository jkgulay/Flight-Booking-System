<?php 

$conn = new mysqli('localhost', 'root', '', 'flight_booking_db');

// Check for connection errors
if ($conn->connect_error) {
    die("Could not connect to MySQL: " . $conn->connect_error);
}
