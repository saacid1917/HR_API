<?php

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if all required parameters are present
    if (isset($input['userID'], $input['skill'], $input['language'], $input['reference'], $input['startDate'], $input['endDate'], $input['certificate'])) {
        
        // Assign values
        $userID = $input['userID'];
        $skill = $input['skill'];
        $language = $input['language'];
        $reference = $input['reference'];
        $startDate = $input['startDate'];
        $endDate = $input['endDate'];
        $certificate = $input['certificate'];

        // Include database connection
        include("dbconnection.php");
        $con = dbconnection();
        
        // Prepare SQL statement
        $query = "INSERT INTO skills (userID, skill, language, reference, startDate, endDate, certificate) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "issssss", $userID, $skill, $language, $reference, $startDate, $endDate, $certificate);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                $response = array(
                    "success" => true,
                    "message" => "Record inserted successfully",
                    "data" => array(
                        "userID" => $userID,
                        "skill" => $skill,
                        "language" => $language,
                        "reference" => $reference,
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                        "certificate" => $certificate
                    )
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Failed to insert record: " . mysqli_error($con)
                );
            }

            mysqli_stmt_close($stmt);
        } else {
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($con)
            );
        }

        mysqli_close($con);

    } else {
        $response = array(
            "success" => false,
            "message" => "Missing required parameters"
        );
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request method: Use POST"
    );
}

// Print the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

?>
