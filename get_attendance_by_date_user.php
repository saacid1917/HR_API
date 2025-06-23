<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("dbconnection.php");

// Get the database connection
$db = dbconnection();

// Check connection
if ($db->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $db->connect_error]));
}

// Ensure the table exists
$tableCheck = $db->query("SHOW TABLES LIKE 'employee_timestamp'");
if ($tableCheck->num_rows == 0) {
    die(json_encode(["error" => "Table 'employee_timestamp' does not exist in the database"]));
}

// Get parameters from request
$userID = $_GET['userID'] ?? null;
$date = $_GET['date'] ?? null;

// Validate inputs
if (!$userID || !$date) {
    die(json_encode(["error" => "Missing parameters: userID and date are required"]));
}

// Modify SQL query to filter by DATE(checkIn)
$sql = "SELECT checkIn, checkOut, status FROM employee_timestamp WHERE userID = ? AND DATE(checkIn) = ?";
$stmt = $db->prepare($sql);

if (!$stmt) {
    die(json_encode(["error" => "SQL preparation failed: " . $db->error]));
}

$stmt->bind_param("ss", $userID, $date);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data
$attendanceData = $result->fetch_assoc() ?? []; // Return empty object if no record found

// Close connections
$stmt->close();
$db->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($attendanceData);
?>
