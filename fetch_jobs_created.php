<?php
include("dbconnection.php");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connect to database
$con = dbconnection();

// Check connection
if ($con->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $con->connect_error]);
    exit();
}

// Query all jobs
$sql = "SELECT 
            id,
            jobTitle,
            department,
            description,
            requirements,
            salary,
            jobType,
            location,
            experienceLevel,
            deadline,
            positions,
            skills,
            benefits,
            gender,
            created_at
        FROM jobs
        ORDER BY id DESC";

$result = $con->query($sql);

// Return results
if ($result && $result->num_rows > 0) {
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    echo json_encode($jobs);
} else {
    echo json_encode([]);
}

$con->close();
?>
