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
    // Read JSON data from POST request
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    // Check if 'userID' is provided in the JSON data
    if (isset($inputData['userID'])) {
        $id = htmlspecialchars($inputData['userID']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Prepare the SQL statement for updating points
        $query = "UPDATE points SET performancePoints=0, seminarPoints=0, attendancePoints=0, totalPoints=0, deductPoints=0 WHERE userID='$id'";

        $response = array();
        if ($con->query($query) === TRUE) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['error'] = $con->error;
        }

        // Return the response
        echo json_encode($response);

        // Close the connection
        $con->close();
    } else {
        // If required parameter is missing
        echo json_encode(array('success' => false, 'error' => 'Missing required parameters'));
    }
} else {
    // Invalid request method
    echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
}
?>
