<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and decode JSON input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the required parameter is present
    if (isset($input['name'])) {
        $jobTitle = htmlspecialchars($input['name']);

        include("dbconnection.php");
        $con = dbconnection();

        $query = "DELETE FROM jobtitle WHERE title = ?";
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $jobTitle);
            if (mysqli_stmt_execute($stmt)) {
                $response = array("success" => true, "message" => "Job Title deleted successfully");
            } else {
                $response = array("success" => false, "message" => "Failed to delete job title: " . mysqli_error($con));
            }
            mysqli_stmt_close($stmt);
        } else {
            $response = array("success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con));
        }
        mysqli_close($con);
    } else {
        $response = array("success" => false, "message" => "Missing required parameters");
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method: POST required");
}

header('Content-Type: application/json');
echo json_encode($response);

?>
