<?php
header('Content-Type: application/json');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Fetch input data (JSON or form-data)
$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($input['title']) ? $input['title'] : (isset($_POST['title']) ? $_POST['title'] : null);
    $place = isset($input['place']) ? $input['place'] : (isset($_POST['place']) ? $_POST['place'] : null);
    $date = isset($input['date']) ? $input['date'] : (isset($_POST['date']) ? $_POST['date'] : null);

    if (!empty($title) && !empty($place) && !empty($date)) {
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection failed"
            ]);
            exit;
        }

        // Sanitize inputs
        $title = htmlspecialchars($title);
        $place = htmlspecialchars($place);
        $date = htmlspecialchars($date);

        // Prepare SQL query
        $query = "INSERT INTO seminar (title, place, date) VALUES (?, ?, ?)";

        // Prepare and bind the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "sss", $title, $place, $date);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                $response = array(
                    "success" => true,
                    "message" => "Record updated successfully"
                );
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Failed to update record: " . mysqli_error($con)
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
            "message" => "Missing required parameters"
        );
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

// Print the response as JSON
echo json_encode($response);
?>
