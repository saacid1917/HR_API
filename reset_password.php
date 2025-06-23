<?php

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json'); 

function dbconnection() {
    $con = mysqli_connect("localhost", "root", "", "hr");
    if (!$con) {
        echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
        exit;
    }
    return $con;
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get input JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input: Email and password required."]);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$password = trim($data['password']); // Store password as plain text (not recommended)

if (!$email) {
    echo json_encode(["success" => false, "message" => "Invalid email format."]);
    exit;
}

$db = dbconnection();

// Prepare SQL statement
$stmt = mysqli_prepare($db, "UPDATE users_table SET password = ? WHERE email = ?");
mysqli_stmt_bind_param($stmt, "ss", $password, $email);
$updated = mysqli_stmt_execute($stmt);

if ($updated && mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(["success" => true, "message" => "Password updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to update password."]);
}

// Close DB connections
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
