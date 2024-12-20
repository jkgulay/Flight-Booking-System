<?php
session_start();
include 'db_connect.php';
// SHOW INDEX FROM booked_flight;
$user_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null;
if ($user_id === null) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['flight_id'])) {
    $flight_id = intval($_GET['flight_id']);
    $flight_query = "SELECT * FROM flight_details WHERE flight_id = $flight_id";
    $flight_result = $conn->query($flight_query);

    if ($flight_result->num_rows > 0) {
        $flight = $flight_result->fetch_assoc();
    } else {
        die('Flight not found.');
    }
} else {
    die('No flight ID provided.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $flight_id = intval($_POST['flight_id']);

    // Validate input
    if (empty($name) || empty($address) || empty($contact)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        // Insert booking into the database
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e9ecef;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }

        h2 {
            color: #343a40;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .flight-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
        }

        .flight-details strong {
            color: #495057;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            margin-bottom: 10px;
            font-size: 1rem;
            color: #495057;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #495057;
        }

        input {
            width: calc(100% - 10px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
 }

        .button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Book Flight</h2>
        <div class="flight-details">
            <p><strong>Flight Details:</strong></p>
            <ul>
                <li><strong>Airlines:</strong> <?php echo htmlspecialchars($flight['airlines']); ?></li>
                <li><strong>From:</strong> <?php echo htmlspecialchars($flight['departure_airport']); ?></li>
                <li><strong>To:</strong> <?php echo htmlspecialchars($flight['arrival_airport']); ?></li>
                <li><strong>Departure:</strong> <?php echo htmlspecialchars($flight['departure_datetime']); ?></li>
                <li><strong>Arrival:</strong> <?php echo htmlspecialchars($flight['arrival_datetime']); ?></li>
                <li><strong>Price:</strong> <?php echo number_format($flight['price'], 2); ?></li>
            </ul>
        </div>

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
    </div>
</body>
</html>