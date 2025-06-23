<?php

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include("dbconnection.php");

// Check if the database connection is successful
if ($db->connect_error) {
    // Database connection failed
    $response = array(
        "success" => false,
        "message" => "Database connection failed: " . $db->connect_error
    );
    // Print the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    // Stop further execution
    exit();
}

// Prepare the SQL statement
$query = "SELECT * FROM points";

// Execute the statement
$result = $db->query($query);

// Check if there are any records
if ($result && $result->num_rows > 0) {
    // Fetch all records and store them in an array
    $records = array();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    // Print the records as JSON
    header('Content-Type: application/json');
    echo json_encode($records);
} else {
    // No records found
    $response = array(
        "success" => false,
        "message" => "No records found"
    );
    // Print the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the database connection
$db->close();
?>
