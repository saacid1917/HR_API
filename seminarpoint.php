<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Decode JSON payload if Content-Type is application/json
if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log received data
    error_log("POST data: " . print_r($_POST, true));

    // Check if the user_id parameter is present
    if (isset($_POST['userID']) && !empty($_POST['userID'])) {
        // Sanitize input data
        $user_id = htmlspecialchars($_POST['userID']);

        // Include the file containing the database connection
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            $response = array(
                "success" => false,
                "message" => "Database connection failed: " . mysqli_connect_error()
            );
            echo json_encode($response);
            exit;
        }

        // Check if the user already exists in the points table
        $check_query = "SELECT seminarPoints FROM points WHERE userID = ?";
        if ($check_stmt = mysqli_prepare($con, $check_query)) {
            mysqli_stmt_bind_param($check_stmt, "i", $user_id);

            if (mysqli_stmt_execute($check_stmt)) {
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    // User exists, fetch current seminar points
                    mysqli_stmt_bind_result($check_stmt, $seminar_points);
                    mysqli_stmt_fetch($check_stmt);
                    mysqli_stmt_close($check_stmt);

                    // Increment the seminar points
                    $seminar_points += 20;

                    // Update seminar points in the database
                    $update_query = "UPDATE points SET seminarPoints = ? WHERE userID = ?";
                    if ($update_stmt = mysqli_prepare($con, $update_query)) {
                        mysqli_stmt_bind_param($update_stmt, "ii", $seminar_points, $user_id);

                        if (mysqli_stmt_execute($update_stmt)) {
                            $response = array(
                                "success" => true,
                                "message" => "Seminar points updated successfully",
                                "new_seminar_points" => $seminar_points
                            );
                        } else {
                            $response = array(
                                "success" => false,
                                "message" => "Failed to update seminar points: " . mysqli_error($con)
                            );
                        }

                        mysqli_stmt_close($update_stmt);
                    } else {
                        $response = array(
                            "success" => false,
                            "message" => "Failed to prepare update statement: " . mysqli_error($con)
                        );
                    }
                } else {
                    // User does not exist, insert a new record
                    mysqli_stmt_close($check_stmt);

                    $seminar_points = 1; // Start with 1 point

                    $insert_query = "INSERT INTO points (userID, seminarPoints) VALUES (?, ?)";
                    if ($insert_stmt = mysqli_prepare($con, $insert_query)) {
                        mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $seminar_points);

                        if (mysqli_stmt_execute($insert_stmt)) {
                            $response = array(
                                "success" => true,
                                "message" => "Seminar points initialized successfully",
                                "new_seminar_points" => $seminar_points
                            );
                        } else {
                            $response = array(
                                "success" => false,
                                "message" => "Failed to initialize seminar points: " . mysqli_error($con)
                            );
                        }

                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $response = array(
                            "success" => false,
                            "message" => "Failed to prepare insert statement: " . mysqli_error($con)
                        );
                    }
                }
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Failed to execute check statement: " . mysqli_error($con)
                );
            }
        } else {
            $response = array(
                "success" => false,
                "message" => "Failed to prepare check statement: " . mysqli_error($con)
            );
        }

        mysqli_close($con);
    } else {
        $response = array(
            "success" => false,
            "message" => "Missing required parameter: userID"
        );
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST required"
    );
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
