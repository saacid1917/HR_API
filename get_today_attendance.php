<?php
include("dbconnection.php");
date_default_timezone_set("Africa/Mogadishu");

$conn = dbconnection(); // ✅ Use this always!

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['userID'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing userID"]);
    exit;
}

$userID = $_GET['userID'];
$today = date('Y-m-d');

$query = "SELECT checkIn, checkOut, checkin_image, checkout_image 
          FROM employee_timestamp 
          WHERE userID = ? AND DATE(createdAt) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $userID, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "checkIn" => $row['checkIn'],
        "checkOut" => $row['checkOut'],
        "checkin_image" => !empty($row['checkin_image']) 
            ? preg_replace('/\s+/', '', $row['checkin_image']) 
            : null,
        "checkout_image" => !empty($row['checkout_image']) 
            ? preg_replace('/\s+/', '', $row['checkout_image']) 
            : null,
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "No attendance record for today"
    ]);
}
?>
