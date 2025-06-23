<?php
// Include database connection file
include("dbconnection.php");
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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

// Get the JSON input
$rawInput = file_get_contents("php://input");

// Decode the JSON input
$jsonInput = json_decode($rawInput, true);

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    sendResponse(false, "Invalid JSON format: " . json_last_error_msg());
}

// Validate JSON input
if (!isset($jsonInput['email'])) {
    sendResponse(false, "Missing required parameter: email");
}

// Extract validated input
$email = filter_var($jsonInput['email'], FILTER_SANITIZE_EMAIL);

if (!$email) {
    sendResponse(false, "Invalid or empty value for email");
}

try {
    // Establish database connection
    $con = dbconnection();
    if (!$con) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Prepare SQL query to check if the email exists
    $query = "SELECT * FROM users_table WHERE email = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($con));
    }

    // Bind parameters and execute query
    mysqli_stmt_bind_param($stmt, "s", $email);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute statement: " . mysqli_error($con));
    }

    // Get the result
    mysqli_stmt_store_result($stmt);

    // Check if any rows were found
    if (mysqli_stmt_num_rows($stmt) > 0) {
        sendResponse(true, "Done");
    } else {
        sendResponse(false, "Email ID doesn't exist");
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
