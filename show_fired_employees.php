<?php
include("dbconnection.php");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable CORS for frontend access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check DB connection
if ($db->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $db->connect_error]);
    exit();
}

// SQL query to join fired employees with employee and users_table
$query = "SELECT 
    f.userID,
    f.department,
    f.firing_date,
    f.reason,
    f.years_worked,
    f.status,
    e.firstName,
    e.lastName,
    e.gender,
    e.address,
    e.contactNumber,
    e.photo,
    u.email
FROM fired_employees f
JOIN employee e ON f.userID = e.userID
JOIN users_table u ON f.userID = u.userID
ORDER BY f.firing_date DESC";

// Execute query
$result = $db->query($query);

// Handle results
if ($result && $result->num_rows > 0) {
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
} else {
    echo json_encode(["success" => false, "message" => "No fired employees found"]);
}

// Close DB
$db->close();
?>
