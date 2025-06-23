<?php 
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON body and decode it
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if all required parameters are present in the JSON data
    if (isset($data['userID']) && isset($data['cashout'])) {
        // Sanitize input data
        $id = htmlspecialchars($data['userID']);
        $cashout = (int)$data['cashout'];  // Convert to integer

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();
        
        // Prepare the SQL statement for updating the cashout field
        $query = "UPDATE points SET cashout = ? WHERE userID = ?";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // Bind the parameters
            mysqli_stmt_bind_param($stmt, "is", $cashout, $id);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Record updated successfully
                $response = array(
                    "success" => true,
                    "message" => "Record updated successfully"
                );
            } else {
                // Failed to execute the statement
                $response = array(
                    "success" => false,
                    "message" => "Failed to update record: " . mysqli_error($con)
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
