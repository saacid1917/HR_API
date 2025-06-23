<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['userID'], $data['seminarID'], $data['attendanceDate'], $data['status'])) {

        $userID = htmlspecialchars($data['userID']);
        $seminarID = htmlspecialchars($data['seminarID']);
        $attendanceDate = htmlspecialchars($data['attendanceDate']);
        $status = htmlspecialchars($data['status']); // Added status field

        include("dbconnection.php");
        $con = dbconnection();

        $query = "INSERT INTO seminar_attendance (userID, seminarID, attendanceDate, status) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "iiss", $userID, $seminarID, $attendanceDate, $status);

            if (mysqli_stmt_execute($stmt)) {
                $response = ["success" => true, "message" => "Attendance record inserted successfully"];
            } else {
                $response = ["success" => false, "message" => "Failed to insert record: " . mysqli_error($con)];
            }

            mysqli_stmt_close($stmt);
        } else {
            $response = ["success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con)];
        }

        mysqli_close($con);

    } else {
        $response = ["success" => false, "message" => "Missing required parameters"];
    }
} else {
    $response = ["success" => false, "message" => "Invalid request method: POST required"];
}

echo json_encode($response);
?>
