<?php
header('Content-Type: application/json');

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection function
function dbconnection() {
    $con = new mysqli('localhost', 'root', '', 'hr');
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }
    return $con;
}

$con = dbconnection();

// Function to handle GET requests
function handleGetRequest($con) {
    $query = "SELECT userID, performancePoints, attendancePoints, seminarPoints FROM points";
    $result = mysqli_query($con, $query);

    if ($result) {
        $points = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode([
            "success" => true,
            "data" => $points
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch data from the database."
        ]);
    }

    mysqli_close($con);
}

// Function to handle POST and PUT requests
function handlePostAndPutRequest($con) {
    $input = json_decode(file_get_contents("php://input"), true);

    // Extract points data from the request
    $userID = htmlspecialchars($input['userID'] ?? '');
    $performancePoints = intval($input['performancePoints'] ?? 0);
    $attendancePoints = intval($input['attendancePoints'] ?? 0);
    $seminarPoints = intval($input['seminarPoints'] ?? 0);

    // Check if user exists in points table
    $check_points_query = "SELECT * FROM points WHERE userID = ?";
    if ($stmt = mysqli_prepare($con, $check_points_query)) {
        mysqli_stmt_bind_param($stmt, "i", $userID);
        mysqli_stmt_execute($stmt);
        $points_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($points_result) > 0) {
            // User exists, update their points
            $update_query = "UPDATE points 
                             SET performancePoints = performancePoints + ?, 
                                 attendancePoints = attendancePoints + ?, 
                                 seminarPoints = seminarPoints + ? 
                             WHERE userID = ?";
            if ($stmt = mysqli_prepare($con, $update_query)) {
                mysqli_stmt_bind_param($stmt, "iiii", $performancePoints, $attendancePoints, $seminarPoints, $userID);
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode([
                        "success" => true,
                        "message" => "Points updated successfully."
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to update points. Please try again later."
                    ]);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // User doesn't exist, insert new user with points
            $insert_query = "INSERT INTO points (userID, performancePoints, attendancePoints, seminarPoints) 
                             VALUES (?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($con, $insert_query)) {
                mysqli_stmt_bind_param($stmt, "iiii", $userID, $performancePoints, $attendancePoints, $seminarPoints);
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode([
                        "success" => true,
                        "message" => "New user added with points."
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Failed to insert new points. Please try again later."
                    ]);
                }
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error preparing the database query. Please try again later."
        ]);
    }

    mysqli_close($con);
}

// Check the request method
$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod === 'GET') {
    handleGetRequest($con);
} elseif ($requestMethod === 'POST' || $requestMethod === 'PUT') {
    handlePostAndPutRequest($con);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Only GET, POST, and PUT are allowed."
    ]);
    exit;
}
?>
