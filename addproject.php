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
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true); // Decode JSON data into an associative array

    // Check if all required parameters are present
    if (
        isset($data['projecttitle']) &&
        isset($data['teamleader']) &&
        isset($data['teammemners']) &&
        isset($data['projectdescription']) &&
        isset($data['technologies']) &&
        isset($data['startdate']) &&
        isset($data['enddate'])
    ) {
        // Sanitize input data
        $projecttitle = htmlspecialchars($data['projecttitle']);
        $teamleader = htmlspecialchars($data['teamleader']);
        $teammemners = htmlspecialchars($data['teammemners']);
        $projectdescription = htmlspecialchars($data['projectdescription']);
        $technologies = htmlspecialchars($data['technologies']);
        $startdate = htmlspecialchars($data['startdate']);
        $enddate = htmlspecialchars($data['enddate']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Prepare the SQL statement for insertion
        $query = "INSERT INTO project (projecttitle, teamleader, teamMembers, projectdescription, technologies, startdate, enddate, createdat) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "sssssss", $projecttitle, $teamleader, $teammemners, $projectdescription, $technologies, $startdate, $enddate);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Record inserted successfully
                $response = array(
                    "success" => true,
                    "message" => "Project added successfully"
                );
            } else {
                // Failed to execute the statement
                $response = array(
                    "success" => false,
                    "message" => "Failed to insert project: " . mysqli_error($con)
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
            "message" => "Missing required parameters. Ensure all fields are provided."
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