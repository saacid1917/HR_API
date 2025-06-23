<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS for cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include the file containing the database connection
include("dbconnection.php");
$conn = dbconnection();

if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]));
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data and decode it as JSON
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the required parameters are present in the decoded JSON
    if (isset($input['taskID'])) {
        // Sanitize input data
        $taskID = htmlspecialchars($input['taskID']);
        $status = "arrived";
        // Retrieve distance from input, default to NULL if not provided or invalid
        $distance = isset($input['distance']) && is_numeric($input['distance']) ? floatval($input['distance']) : NULL;

        // Prepare the SQL statement for updating the task status, arrival time, and distance
        // Make sure your 'task' table has a column named 'distance_to_task' with a suitable data type (e.g., DOUBLE, DECIMAL, FLOAT)
        $query = "UPDATE task SET status = ?, completeTime = NOW(), distance = ? WHERE taskID = ?";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($conn, $query)) {
            // Bind parameters - "ssd" means string, string, double (adjust 'd' if distance_to_task is a different numeric type like integer 'i')
            mysqli_stmt_bind_param($stmt, "ssd", $status, $distance, $taskID);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Successful execution, send a success response
                $response = array(
                    "success" => true,
                    "message" => "Record updated successfully with arrival status and distance"
                );
            } else {
                // Failed to execute the statement, send an error response
                $response = array(
                    "success" => false,
                    "message" => "Failed to update record: " . mysqli_error($conn)
                );
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            // Failed to prepare the statement, send an error response
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($conn)
            );
        }
    } else {
        // Missing required parameters, send an error response
        $response = array(
            "success" => false,
            "message" => "Missing required parameters: taskID"
        );
    }
} else {
    // Invalid request method, send an error response
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

// Close the database connection
mysqli_close($conn);

// Print the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>