<?php
include("dbconnection.php");


// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if the database connection is successful
if ($db->connect_error) {
    $response = array(
        "success" => false,
        "message" => "Database connection failed: " . $db->connect_error
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Prepare the SQL statement
$query = "SELECT * FROM employee_timestamp";
$result = $db->query($query);

if (!$result) {
    $response = array(
        "success" => false,
        "message" => "Query failed: " . $db->error
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($result->num_rows > 0) {
    $records = array();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode(array(
        "success" => true,
        "data" => $records
    ));
} else {
    $response = array(
        "success" => false,
        "message" => "No records found"
    );
    header('Content-Type: application/json');
    echo json_encode($response);
}

$db->close();
?>
