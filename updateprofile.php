<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable CORS (for cross-origin requests)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');

    // Log the raw POST data for debugging
    file_put_contents("php_debug.log", "Raw Data: " . $rawData . PHP_EOL, FILE_APPEND);

    // Decode the JSON data
    $data = json_decode($rawData, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response = array(
            "success" => false,
            "message" => "JSON decoding error: " . json_last_error_msg()
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Validate required parameter: userID is mandatory for update
    if (empty($data['userID'])) {
        $response = array(
            "success" => false,
            "message" => "Missing required parameter: userID"
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Sanitize input data
    $userID = htmlspecialchars(trim($data['userID']));

    $updateFields = [];
    $paramTypes = "";
    $params = [];

    // Prepare fields to update dynamically
    if (isset($data['firstName'])) {
        $firstName = htmlspecialchars(trim($data['firstName']));
        $updateFields[] = "firstName = ?";
        $paramTypes .= "s";
        $params[] = &$firstName;
    }
    if (isset($data['lastName'])) {
        $lastName = htmlspecialchars(trim($data['lastName']));
        $updateFields[] = "lastName = ?";
        $paramTypes .= "s";
        $params[] = &$lastName;
    }
    if (isset($data['address'])) {
        $address = htmlspecialchars(trim($data['address']));
        $updateFields[] = "address = ?";
        $paramTypes .= "s";
        $params[] = &$address;
    }
    if (isset($data['birthdate'])) {
        $birthdate = htmlspecialchars(trim($data['birthdate']));
        $updateFields[] = "birthdate = ?";
        $paramTypes .= "s";
        $params[] = &$birthdate;
    }
    if (isset($data['photo'])) {
        $photo = htmlspecialchars(trim($data['photo']));
        $updateFields[] = "photo = ?";
        $paramTypes .= "s";
        $params[] = &$photo;
    }

    // Check if there are any fields to update
    if (empty($updateFields)) {
        $response = array(
            "success" => false,
            "message" => "No fields to update provided."
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Include the file containing the database connection
    include("dbconnection.php");
    $con = dbconnection();

    // Prepare the SQL statement for updating dynamically
    $query = "UPDATE employee SET " . implode(", ", $updateFields) . " WHERE userID = ?";
    $paramTypes .= "s"; // For userID (string)
    $params[] = &$userID;

    // Prepare and bind the statement
    if ($stmt = mysqli_prepare($con, $query)) {

        // Use call_user_func_array to bind parameters dynamically
        array_unshift($params, $stmt, $paramTypes);
        call_user_func_array('mysqli_stmt_bind_param', $params);


        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Check if any rows were affected
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $response = array(
                    "success" => true,
                    "message" => "Record updated successfully"
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "No record updated. Check if the userID exists or data is the same."
                );
            }
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