<?php
include("dbconnection.php");

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
        $userID = htmlspecialchars($inputData['userID']);

        // Establish database connection
        $con = dbconnection();

        // Prepare the SQL query to fetch data based on userID
        $query = "SELECT * FROM points WHERE userID = '$userID'";

        // Execute the query
        $result = $con->query($query);

        // Check if the query was successful
        if ($result && $result->num_rows > 0) {
            // Fetch the data and store it in an array
            $records = array();
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }

            // Respond with the data as JSON
            header('Content-Type: application/json');
            echo json_encode($records);
        } else {
            // If no records are found
            $response = array(
                "success" => false,
                "message" => "No records found for userID: $userID"
            );
            echo json_encode($response);
        }

        // Close the database connection
        $con->close();
    } else {
        // If userID is not provided in the request
        $response = array(
            "success" => false,
            "message" => "Missing required parameter: userID"
        );
        echo json_encode($response);
    }
} else {
    // If the request method is not POST
    $response = array(
        "success" => false,
        "message" => "Invalid request method. POST required."
    );
    echo json_encode($response);
}
?>
