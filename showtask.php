<?php
include("dbconnection.php");

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Database connection
$con = dbconnection();
if (!$con) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

// Prepare the SQL query with JOIN
$query = "
    SELECT task.*, employee.firstName 
    FROM task
    JOIN employee ON task.userID = employee.userID
";

$result = mysqli_query($con, $query);

// Handle query failure
if (!$result) {
    echo json_encode(["success" => false, "message" => "Query failed: " . mysqli_error($con)]);
    exit();
}

// Fetch results
$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[] = $row;
}

// Always return a JSON array (even if empty)
if (empty($records)) {
    echo json_encode([]);
} else {
    echo json_encode($records, JSON_PRETTY_PRINT);
}

// Close database connection
mysqli_close($con);
?>
