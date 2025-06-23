<?php
include("dbconnection.php");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Create DB connection
$con = dbconnection();

// Check connection
if ($con->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $con->connect_error]);
    exit();
}

// Retrieve POST values
$jobTitle = $_POST['jobTitle'];
$department = $_POST['department'];
$description = $_POST['description'];
$requirements = $_POST['requirements'];
$salary = $_POST['salary'];
$jobType = $_POST['jobType'];
$location = $_POST['location'];
$experienceLevel = $_POST['experienceLevel'];
$deadline = $_POST['deadline'];
$positions = $_POST['positions'];
$skills = $_POST['skills'];
$benefits = $_POST['benefits'];
$gender = $_POST['gender'];

// Prepare SQL
$sql = "INSERT INTO jobs (
  jobTitle, department, description, requirements, salary,
  jobType, location, experienceLevel, deadline,
  positions, skills, benefits, gender
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $con->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Statement preparation failed: " . $con->error]);
    exit();
}

// Bind values
$stmt->bind_param(
  "sssssssssssss",
  $jobTitle, $department, $description, $requirements, $salary,
  $jobType, $location, $experienceLevel, $deadline,
  $positions, $skills, $benefits, $gender
);

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Job created successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to create job: " . $stmt->error]);
}

// Close
$stmt->close();
$con->close();
?>
