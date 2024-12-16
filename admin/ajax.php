<?php
ob_start();
header('Content-Type: application/json');

include 'admin_class.php';
$crud = new Action();

function sendJsonResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

try {
    if (!isset($_GET['action'])) {
        sendJsonResponse('error', 'No action specified');
    }

    $action = $_GET['action'];

    $actionMap = [
        'login' => 'login',
        'login2' => 'login2',
        'logout' => 'logout',
        'logout2' => 'logout2',
        'save_user' => 'save_user',
        'delete_user' => 'delete_user',
        'signup' => 'signup',
        'save_settings' => 'save_settings',
        'save_airlines' => 'save_airlines',
        'delete_airlines' => 'delete_airlines',
        'save_airports' => 'save_airports',
        'delete_airports' => 'delete_airports',
        'save_flight' => 'save_flight',
        'delete_flight' => 'delete_flight',
        'book_flight' => 'book_flight',
        'update_booked' => 'update_booked'
    ];

    if (!array_key_exists($action, $actionMap)) {
        sendJsonResponse('error', 'Invalid action');
    }

    $methodName = $actionMap[$action];
    $result = $crud->$methodName();

    if ($result === false) {
        sendJsonResponse('error', 'Operation failed');
    } elseif (is_string($result)) {
        $parsedResult = json_decode($result, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo $result;
        } else {
            sendJsonResponse('success', $result);
        }
    } else {
        sendJsonResponse('success', 'Operation completed', $result);
    }

} catch (Exception $e) {
    sendJsonResponse('error', 'An unexpected error occurred: ' . $e->getMessage());
}

ob_end_flush();