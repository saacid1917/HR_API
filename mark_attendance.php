<?php
date_default_timezone_set("Africa/Mogadishu");
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Required fields
$userID = $_POST['userID'];
$image = $_POST['image'];
$now = $_POST['timestamp'];
$today = date('Y-m-d');

if (empty($userID) || empty($image)) {
    echo json_encode(["success" => false, "message" => "Missing userID or image"]);
    exit;
}

// Check if there's already a record for today
$query = "SELECT * FROM employee_timestamp WHERE userID = ? AND DATE(createdAt) = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("is", $userID, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No record yet → Insert new Check-In
    $insert = "INSERT INTO employee_timestamp (userID, checkIn, checkin_image, status, createdAt, updatedAt)
               VALUES (?, ?, ?, 'present', ?, ?)";
    $stmt = $db->prepare($insert);
    $stmt->bind_param("issss", $userID, $now, $image, $now, $now);
    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "action" => "checkin",
        "checkIn" => $now,
        "checkin_image" => $image  // just return as-is
    ]);
} else {
    $row = $result->fetch_assoc();
    if (empty($row['checkOut'])) {
        // Already checked in → do Check-Out
        $update = "UPDATE employee_timestamp SET checkOut = ?, checkout_image = ?, updatedAt = ? WHERE timestampID = ?";
        $stmt = $db->prepare($update);
        $stmt->bind_param("sssi", $now, $image, $now, $row['timestampID']);
        $success = $stmt->execute();

        echo json_encode([
            "success" => $success,
            "action" => "checkout",
            "checkOut" => $now,
            "checkout_image" => $image  // just return as-is
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Already checked out today"]);
    }
}
?>
