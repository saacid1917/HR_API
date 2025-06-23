<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
error_log('Received input: ' . print_r($input, true));

if (isset($input['qr_code']) && isset($input['counter']) && isset($input['status']) && isset($input['timestamp'])) {
    $entry = [
        'qr_code' => $input['qr_code'],
        'counter' => $input['counter'],
        'status' => $input['status'],
        'timestamp' => $input['timestamp']
    ];


    error_log('qr_code: ' . $entry['qr_code']);
    error_log('counter: ' . $entry['counter']);
    error_log('status: ' . $entry['status']);
    error_log('timestamp: ' . $entry['timestamp']);

    if (!isset($_SESSION['qr_history'])) {
        $_SESSION['qr_history'] = [];
    }

    $_SESSION['qr_history'][] = $entry;

    if ($entry['status'] === 'waiting' || $entry['status'] === 'stopped') {
        // Flush session and clear all variables
        $_SESSION['qr_history'] = [];
        session_unset();
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Session and variables cleared due to status being waiting or stopped',
            'history' => []
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'QR saved',
            'history' => $_SESSION['qr_history'],
            'data' => $entry
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid QR data',
        'received' => $input
    ]);
}
?>
