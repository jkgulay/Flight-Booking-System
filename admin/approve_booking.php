<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        echo json_encode(['message' => 'Invalid request. Missing parameters.']);
        exit;
    }

    $id = intval($_POST['id']); 
    $action = $_POST['action'];

    try {
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE booked_flight SET status = 'accepted' WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Booking approved successfully.']);
            } else {
                echo json_encode(['message' => 'No booking found with the provided ID or status already updated.']);
            }
        } elseif ($action === 'decline') {
            $stmt = $conn->prepare("UPDATE booked_flight SET status = 'declined' WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Booking declined successfully.']);
            } else {
                echo json_encode(['message' => 'No booking found with the provided ID or status already updated.']);
            }
        } else {
            echo json_encode(['message' => 'Invalid action.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>