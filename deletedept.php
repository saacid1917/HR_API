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
    // Check if the deptname parameter is present in the POST request
    if (isset($_POST['deptname'])) {
        // Sanitize input data
        $deptname = htmlspecialchars($_POST['deptname']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Prepare the SQL statement for deleting
        $query = "DELETE FROM dept WHERE deptName = ?";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $deptname);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                $response = array(
                    "success" => true,
                    "message" => "Department deleted successfully"
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Failed to delete department: " . mysqli_error($con)
                );
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($con)
            );
        }

        // Close the database connection
        mysqli_close($con);
    } else {
        $response = array(
            "success" => false,
            "message" => "Missing required parameter: deptname"
        );
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

// Print the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

?>
