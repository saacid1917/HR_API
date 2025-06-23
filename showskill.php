<?php
include("dbconnection.php");

// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");  // Allow access from all domains (for testing purposes)
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
$query = "SELECT * FROM skills";

// Execute the statement
$result = $db->query($query);

// Check if there are any records
if ($result && $result->num_rows > 0) {
    // Fetch all records and store them in an array
    $records = array();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    // Return the records as JSON
    header('Content-Type: application/json');
    echo json_encode($records);
} else {
    // No records found
    $response = array(
        "success" => false,
        "message" => "No records found"
    );
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the database connection
$db->close();
?>
