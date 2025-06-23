<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Fetch input from GET or POST
$taskID = $_GET['taskID'] ?? ($_POST['taskID'] ?? null);

// Validate taskID
if ($taskID === null || !is_numeric($taskID) || intval($taskID) <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid or missing taskID"]);
    exit;
}

$taskID = intval($taskID); // Convert to integer

include("dbconnection.php");
$con = dbconnection();

if (!$con) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// Fetch start location details
$query = "SELECT startLatitude, startLongitude, startDate FROM task WHERE taskID = ?";

if ($stmt = mysqli_prepare($con, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $taskID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $response = [
            "success" => true,
            "data" => [
                "taskID" => $taskID,
                "startLatitude" => $row['startLatitude'],
                "startLongitude" => $row['startLongitude'],
                "startDate" => $row['startDate']
            ]
        ];
    } else {
        $response = ["success" => false, "message" => "Task not found"];
    }

    mysqli_stmt_close($stmt);
} else {
    $response = ["success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con)];
}

mysqli_close($con);
echo json_encode($response);
?>