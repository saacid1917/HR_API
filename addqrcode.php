<?php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if QR code is present in the POST request
    if (isset($_POST['qrcode'])) {
        // Sanitize input data
        $qrcode = htmlspecialchars($_POST['qrcode']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        // Check if the connection is successful
        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Prepare the SQL statement for inserting
        $query = "INSERT INTO qrcode (qrcode) VALUES (?)";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $qrcode);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Record inserted successfully
                $response = array(
                    "success" => true,
                    "message" => "QR code inserted successfully"
                );
            } else {
                // Failed to execute the statement
                $response = array(
                    "success" => false,
                    "message" => "Failed to insert QR code: " . mysqli_error($con),
                    "error" => mysqli_stmt_error($stmt)
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
        // Missing QR code parameter
        $response = array(
            "success" => false,
            "message" => "Missing QR code parameter",
            "received_data" => $_POST // Add received data for debugging
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