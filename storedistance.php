<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Fetch input data (JSON or form-data)
$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskID = isset($input['taskID']) ? intval($input['taskID']) : null;
    $distance = isset($input['distance']) ? floatval($input['distance']) : null;

    if ($taskID !== null && $taskID > 0 && $distance !== null) {
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            echo json_encode(["success" => false, "message" => "Database connection failed"]);
            exit;
        }

        // Prepare SQL query to update distance
        $query = "UPDATE task SET distance = ? WHERE taskID = ?";

        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "di", $distance, $taskID);

            if (mysqli_stmt_execute($stmt)) {
                $response = ["success" => true, "message" => "Distance updated successfully"];
            } else {
                $response = ["success" => false, "message" => "Failed to update distance: " . mysqli_error($con)];
            }

            mysqli_stmt_close($stmt);
        } else {
            $response = ["success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con)];
        }

        mysqli_close($con);
    } else {
        $response = ["success" => false, "message" => "Invalid taskID or distance"];
    }
} else {
    $response = ["success" => false, "message" => "Invalid request method: Use POST"];
}

// Print the response as JSON
echo json_encode($response);
?>
