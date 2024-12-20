<?php
session_start();
include 'db_connect.php';

if (isset($_GET['flight_id'])) {
    $flight_id = intval($_GET['flight_id']);
    $flight_query = "SELECT * FROM flight_details WHERE flight_id = $flight_id";
    $flight_result = $conn->query($flight_query);

    if ($flight_result->num_rows > 0) {
        $flight = $flight_result->fetch_assoc();
    } else {
        die('Flight not found.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; 
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $flight_id = intval($_POST['flight_id']);

    if (empty($name) || empty($address) || empty($contact)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        $booking_query = "INSERT INTO booked_flight (flight_id, user_id, name, address, contact, status) 
                          VALUES ('$flight_id', '$user_id', '$name', '$address', '$contact', 'pending')";

        if ($conn->query($booking_query)) {
            echo "<script>alert('Booking successful!'); window.location.href = 'index.php';</script>";
        } else {
            echo "<script>alert('Booking failed. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        h2 {
            color: #213555;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 10px;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: calc(100% - 10px);
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Book Flight</h2>
    <p><strong>Flight Details:</strong></p>
    <ul>
        <li><strong>Airlines:</strong> <?php echo htmlspecialchars($flight['airlines']); ?></li>
        <li><strong>From:</strong> <?php echo htmlspecialchars($flight['departure_airport']); ?></li>
        <li><strong>To:</strong> <?php echo htmlspecialchars($flight['arrival_airport']); ?></li>
        <li><strong>Departure:</strong> <?php echo htmlspecialchars($flight['departure_datetime']); ?></li>
        <li><strong>Arrival:</strong> <?php echo htmlspecialchars($flight['arrival_datetime']); ?></li>
        <li><strong>Price:</strong> <?php echo number_format($flight['price'], 2); ?></li>
    </ul>

    <form method="POST" action="">
        <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>
        
        <label for="contact">Contact:</label>
        <input type="text" id="contact" name="contact" required>
        
        <button type="submit">Book Flight</button>
    </form>
</body>
</html>
