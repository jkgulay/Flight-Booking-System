<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        echo json_encode(['message' => 'Invalid request. Missing parameters.']);
        exit;
    }

    $id = intval($_POST['id']); 
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update = $conn->query("UPDATE booked_flight SET status = 'accepted' WHERE id = $id");
        if ($update) {
            echo json_encode(['message' => 'Booking approved successfully.']);
        } else {
            echo json_encode(['message' => 'Failed to approve the booking.']);
        }
    } elseif ($action === 'decline') {
        $update = $conn->query("UPDATE booked_flight SET status = 'decline' WHERE id = $id");
        if ($update) {
            echo json_encode(['message' => 'Booking declined successfully.']);
        } else {
            echo json_encode(['message' => 'Failed to decline the booking.']);
        }
    } else {
        echo json_encode(['message' => 'Invalid action.']);
    }
    exit;
}
?>
