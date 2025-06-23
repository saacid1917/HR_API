<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data
    $rawInput = file_get_contents("php://input");

    // Try to decode JSON data
    $data = json_decode($rawInput, true);

    // If JSON decoding fails, fall back to form data
    if ($data === null) {
        $data = $_POST;
    }

    // Check if all required parameters are present
    if (isset($data['userID'], $data['leaveType'], $data['reason'], $data['startDate'], $data['endDate'], $data['status'])) {
        // Sanitize input data
        $userID = htmlspecialchars($data['userID']);
        $leaveType = htmlspecialchars($data['leaveType']);
        $reason = htmlspecialchars($data['reason']);
        $startDate = htmlspecialchars($data['startDate']);
        $endDate = htmlspecialchars($data['endDate']);
        $status = htmlspecialchars($data['status']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Prepare the SQL statement
        $query = "
            INSERT INTO leavetable (userID, leaveType, reason, startDate, endDate, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "isssss", $userID, $leaveType, $reason, $startDate, $endDate, $status);

            if (mysqli_stmt_execute($stmt)) {
                // Record inserted successfully
                $response = array(
                    "success" => true,
                    "message" => "Record inserted successfully",
                    "data" => array(
                        "userID" => $userID,
                        "leaveType" => $leaveType,
                        "reason" => $reason,
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                        "status" => $status
                    )
                );
            } else {
                // Failed to execute the statement
                $response = array(
                    "success" => false,
                    "message" => "Failed to insert record: " . mysqli_error($con)
                );
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            // Failed to prepare the statement
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($con)
            );
        }

        // Close the database connection
        mysqli_close($con);
    } else {
        // Missing required parameters
        $response = array(
            "success" => false,
            "message" => "Missing required parameters"
        );
    }
} else {
    // Invalid request method
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

// Print the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

?>
