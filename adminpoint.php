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
    $data = file_get_contents("php://input");

    // Decode the JSON data
    $jsonData = json_decode($data, true);

    // Check if required parameters are present
    if (isset($jsonData['totalPoints'], $jsonData['deductionPoints'], $jsonData['employeeid'])) {
        // Sanitize input data
        $employeeid = intval($jsonData['employeeid']);
        $totalPoints = intval($jsonData['totalPoints']);
        $deductionPoints = intval($jsonData['deductionPoints']);

        // Validate that input parameters are positive integers
        if ($employeeid <= 0 || $totalPoints < 0 || $deductionPoints < 0) {
            http_response_code(400);
            $response = array(
                "success" => false,
                "message" => "Invalid input parameters",
                "received_data" => $jsonData
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Calculate points per category
        $pointsPerCategory = floor($totalPoints / 3);

        // Include the database connection
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            http_response_code(500);
            $response = array(
                "success" => false,
                "message" => "Failed to connect to the database"
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Check if a record for the employee already exists
        $check_query = "SELECT performancePoints, seminarPoints, attendancePoints FROM points WHERE userid = ?";
        $stmt = mysqli_prepare($con, $check_query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $employeeid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $existingPerformancePoints, $existingSeminarPoints, $existingAttendancePoints);
            $recordExists = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($recordExists) {
                // Update the existing record
                $newPerformancePoints = $existingPerformancePoints + $pointsPerCategory;
                $newSeminarPoints = max(0, $existingSeminarPoints - $deductionPoints);
                $newAttendancePoints = max(0, $existingAttendancePoints - $deductionPoints);

                $update_query = "UPDATE points SET performancePoints = ?, seminarPoints = ?, attendancePoints = ? WHERE userid = ?";
                $update_stmt = mysqli_prepare($con, $update_query);

                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "iiii", $newPerformancePoints, $newSeminarPoints, $newAttendancePoints, $employeeid);
                    if (mysqli_stmt_execute($update_stmt)) {
                        $response = array(
                            "success" => true,
                            "message" => "Points updated successfully"
                        );
                    } else {
                        http_response_code(500);
                        $response = array(
                            "success" => false,
                            "message" => "Failed to update points: " . mysqli_error($con)
                        );
                    }
                    mysqli_stmt_close($update_stmt);
                } else {
                    http_response_code(500);
                    $response = array(
                        "success" => false,
                        "message" => "Failed to prepare update statement"
                    );
                }
            } else {
                // Insert a new record
                $insert_query = "INSERT INTO points (userid, performancePoints, seminarPoints, attendancePoints) VALUES (?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($con, $insert_query);

                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "iiii", $employeeid, $pointsPerCategory, -$deductionPoints, -$deductionPoints);
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $response = array(
                            "success" => true,
                            "message" => "Record created successfully"
                        );
                    } else {
                        http_response_code(500);
                        $response = array(
                            "success" => false,
                            "message" => "Failed to create record: " . mysqli_error($con)
                        );
                    }
                    mysqli_stmt_close($insert_stmt);
                } else {
                    http_response_code(500);
                    $response = array(
                        "success" => false,
                        "message" => "Failed to prepare insert statement"
                    );
                }
            }
        } else {
            http_response_code( 500);
            $response = array(
                "success" => false,
                "message" => "Failed to prepare check statement"
            );
        }

        // Close the database connection
        mysqli_close($con);
    } else {
        // Missing required parameters
        http_response_code(400);
        $response = array(
            "success" => false,
            "message" => "Missing required parameters",
            "received_data" => $jsonData
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
} else {
    // Invalid request method
    http_response_code(405);
    $response = array(
        "success" => false,
        "message" => "Method not allowed"
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
