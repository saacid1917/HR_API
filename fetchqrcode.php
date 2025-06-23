<?php
include("dbconnection.php");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check if the database connection is successful
if ($db->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $db->connect_error]);
    exit();
}

// Prepare the SQL query to fetch the latest QR code based on created_at
$query = "SELECT timeID, qrCode, date FROM company_time ORDER BY date DESC LIMIT 1";
$result = $db->query($query);

// Check if a record exists
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Return the latest QR code data with the timeID and created_at
    header('Content-Type: application/json');
    echo json_encode([
        "timeID" => $row['timeID'],
        "qrCode" => $row['qrCode'],
        "created_at" => $row['date']
    ]);         
} else {
    // No records found
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "No QR code found"]);
}

// Close the database connection
$db->close();
?>
