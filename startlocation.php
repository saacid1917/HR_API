<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskID = isset($input['taskID']) ? intval($input['taskID']) : null;
    $startDate = isset($input['startDate']) ? $input['startDate'] : null;
    $startLatitude = isset($input['startLatitude']) ? floatval($input['startLatitude']) : null;
    $startLongitude = isset($input['startLongitude']) ? floatval($input['startLongitude']) : null;

    if ($taskID !== null && $startLatitude !== null && $startLongitude !== null) {
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            echo json_encode(["success" => false, "message" => "Database connection failed"]);
            exit;
        }

        $query = "UPDATE task SET 
                  startDate = ?, 
                  startLatitude = ?, 
                  startLongitude = ?,
                  status = 'start_In'
                  WHERE taskID = ?";

        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "sddi", $startDate, $startLatitude, $startLongitude, $taskID);

            if (mysqli_stmt_execute($stmt)) {
                $response = [
                    "success" => true, 
                    "message" => "Start location stored successfully",
                    "data" => [
                        "taskID" => $taskID,
                        "startDate" => $startDate,
                        "startLatitude" => $startLatitude,
                        "startLongitude" => $startLongitude
                    ]
                ];
            } else {
                $response = ["success" => false, "message" => "Failed to update: " . mysqli_error($con)];
            }

            mysqli_stmt_close($stmt);
        } else {
            $response = ["success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con)];
        }

        mysqli_close($con);
    } else {
        $response = ["success" => false, "message" => "Invalid input parameters"];
    }
} else {
    $response = ["success" => false, "message" => "Invalid request method: Use POST"];
}

echo json_encode($response);
?>