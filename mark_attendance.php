<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Required fields
$userID = $_POST['userID'];
$image = $_POST['image'];
$now = $_POST['timestamp']; // Timestamp from the device (e.g., ISO format like "2025-06-26T21:00:00Z")

if (empty($userID) || empty($image) || empty($now)) {
    echo json_encode(["success" => false, "message" => "Missing userID, image, or timestamp"]);
    exit;
}

// Determine 'today' based on the device timestamp, NOT server time
$today = date('Y-m-d', strtotime($now));

// Check if there's already a record for this date
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
        "checkin_image" => $image
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
            "checkout_image" => $image
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Already checked out today"
        ]);
    }
}
?>
