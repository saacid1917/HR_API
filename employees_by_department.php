<?php
include("dbconnection.php");

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Check for 'dept' parameter
if (!isset($_GET['dept'])) {
    echo json_encode(["success" => false, "message" => "Missing department parameter"]);
    exit();
}

$departmentName = mysqli_real_escape_string($db, $_GET['dept']);

$query = "
SELECT 
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
WHERE f.userID IS NULL AND d.deptName = '$departmentName'
";

$result = $db->query($query);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Query failed: " . $db->error]);
    exit();
}

$employees = [];

while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode($employees);
$db->close();
