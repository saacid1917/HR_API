<?php
include("dbconnection.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable CORS (for cross-origin requests)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if the database connection is successful
if ($db->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $db->connect_error]);
    exit();
}

// Updated query to exclude fired employees
$query = "SELECT 
    e.userID, 
    e.firstName, 
    e.lastName, 
    e.birthdate,
    e.gender, 
    e.address, 
    e.netSalary, 
    e.photo, 
    e.contactNumber, 
    e.deptID, 
    u.email, 
    d.deptName,
    e.created_at,
    (SELECT title FROM jobtitle j WHERE j.departmentID = d.deptID LIMIT 1) AS title
FROM employee e
JOIN users_table u ON e.userID = u.userID
JOIN dept d ON e.deptID = d.deptID
LEFT JOIN fired_employees f ON e.userID = f.userID AND f.status = 'Fired'
WHERE f.userID IS NULL";

// Execute query
$result = $db->query($query);

// Check for SQL errors
if (!$result) {
    die(json_encode(["success" => false, "message" => "Query failed: " . $db->error]));
}

// Fetch and return records
if ($result->num_rows > 0) {
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
} else {
    echo json_encode(["success" => false, "message" => "No records found"]);
}

// Close the database connection
$db->close();
?>
