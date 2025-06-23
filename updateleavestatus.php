<?php
// Include database connection file
include("dbconnection.php");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Function to send JSON response
function sendResponse($success, $message) {
    header('Content-Type: application/json');
    echo json_encode(array("success" => $success, "message" => $message));
    exit;
}

// Get raw POST data
$rawInput = file_get_contents("php://input");

// Log raw input for debugging
error_log("Raw Input: " . $rawInput);

// Decode the raw JSON data sent from Flutter
$jsonInput = json_decode($rawInput, true);

// Log decoded JSON for debugging
error_log("Decoded JSON: " . print_r($jsonInput, true));

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    sendResponse(false, "Invalid JSON format: " . json_last_error_msg());
}

// Validate JSON input
if (!isset($jsonInput['userid']) || !isset($jsonInput['status'])) {
    sendResponse(false, "Missing required parameters: userid and status");
}

// Extract validated input
$userid = filter_var($jsonInput['userid'], FILTER_VALIDATE_INT);
$status = filter_var($jsonInput['status'], FILTER_SANITIZE_SPECIAL_CHARS);

if (!$userid || !$status) {
    sendResponse(false, "Invalid or empty values for userid or status");
}

try {
    // Establish database connection
    $con = dbconnection();
    if (!$con) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Correct SQL query using userid
    $query = "UPDATE leavetable SET status = ? WHERE leaveID = ?";

    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($con));
    }

    // Bind parameters and execute query
    mysqli_stmt_bind_param($stmt, "si", $status, $userid);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute statement: " . mysqli_error($con));
    }

    // Check if any rows were updated
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        sendResponse(true, "Record updated successfully");
    } else {
        sendResponse(false, "No record found with the provided userid");
    }
} catch (Exception $e) {
    // Handle exceptions and send response
    sendResponse(false, "Error: " . $e->getMessage());
} finally {
    // Clean up resources
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    if (isset($con)) {
        mysqli_close($con);
    }
}
?>
