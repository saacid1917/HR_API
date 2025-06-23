<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS for cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data and decode it as JSON
    $input = json_decode(file_get_contents('php://input'), true);

    // Debugging: Log the raw input and decoded JSON to the error log
    error_log("Received input: " . file_get_contents('php://input'));
    error_log("Decoded input: " . print_r($input, true));

    // Check if the required parameters are present in the decoded JSON
    if (isset($input['taskID']) && isset($input['startDate'])) {
        // Sanitize input data
        $taskID = htmlspecialchars($input['taskID']);
        $startDate = htmlspecialchars($input['startDate']);
        $status = "start_In";

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Check if connection is successful
        if (!$con) {
            $response = array(
                "success" => false,
                "message" => "Database connection failed: " . mysqli_connect_error()
            );
            echo json_encode($response);
            exit;
        }
        
        // Prepare the SQL statement for updating the task status and completion time
        $query = "UPDATE task SET status = ?, startDate = ? WHERE taskID = ?";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "sss", $status, $startDate, $taskID);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Successful execution, send a success response
                $response = array(
                    "success" => true,
                    "message" => "Record updated successfully"
                );
            } else {
                // Failed to execute the statement, send an error response
                $response = array(
                    "success" => false,
                    "message" => "Failed to update record: " . mysqli_error($con)
                );
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            // Failed to prepare the statement, send an error response
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($con)
            );
        }

        // Close the database connection
        mysqli_close($con);

    } else {
        // Missing required parameters, send an error response
        $response = array(
            "success" => false,
            "message" => "Missing required parameters"
        );
    }
} else {
    // Invalid request method, send an error response
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

// Print the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
