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

// Check DB connection
if ($db->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $db->connect_error]);
    exit();
}

// Query to get all unique departments (excluding fired employees)
$query = "SELECT DISTINCT d.deptName 
          FROM employee e
          JOIN dept d ON e.deptID = d.deptID
          LEFT JOIN fired_employees f ON e.userID = f.userID AND f.status = 'Fired'
          WHERE f.userID IS NULL";

$result = $db->query($query);

// Handle query failure
if (!$result) {
    echo json_encode(["success" => false, "message" => "Query failed: " . $db->error]);
    exit();
}

// Return the departments
$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['deptName'];
}

echo json_encode($departments);

// Close connection
$db->close();
?>
