<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $missing_params = [];
    $required_params = ['checkInTime', 'checkOutTime', 'date', 'qrcode'];

    // Check for missing parameters
    foreach ($required_params as $param) {
        if (!isset($input[$param])) {
            $missing_params[] = $param;
        }
    }

    // If there are missing parameters, respond with an error
    if (!empty($missing_params)) {
        $response = array(
            "success" => false,
            "message" => "Missing required parameters: " . implode(', ', $missing_params)
        );
    } else {
        $checkInTime = htmlspecialchars($input['checkInTime']);
        $checkOutTime = htmlspecialchars($input['checkOutTime']);
        $date = htmlspecialchars($input['date']);
        $qrcode = htmlspecialchars($input['qrcode']);

        include("dbconnection.php");
        $con = dbconnection();

        $query = "INSERT INTO company_time (checkIn, checkOut, date, qrCode) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "ssss", $checkInTime, $checkOutTime, $date, $qrcode);

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
            mysqli_stmt_close($stmt);
        } else {
            $response = array(
                "success" => false,
                "message" => "Failed to prepare statement: " . mysqli_error($con)
            );
        }
        mysqli_close($con);
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Invalid request method: POST method required"
    );
}

header('Content-Type: application/json');
echo json_encode($response);
?>
