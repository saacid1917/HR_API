<?php
header('Content-Type: application/json');
error_reporting(0); // you can set to E_ALL while debugging
ini_set('display_errors', 0);

include("dbconnection.php");

$userID = $_POST['userID'] ?? '';
$face_image = $_POST['face_image'] ?? '';

if (empty($userID) || empty($face_image)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$query = "UPDATE employee SET face_image = ? WHERE userID = ?";
$stmt = $db->prepare($query); // ✅ use $db instead of $conn

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $db->error]);
    exit;
}

$stmt->bind_param("ss", $face_image, $userID);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}
?>
