<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$receiver_id = $_POST['receiver_id'] ?? '';
$message     = $_POST['message'] ?? '';
$type        = $_POST['type'] ?? 'text';

if (empty($receiver_id) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$sender_id = 'admin';
$timestamp = date("Y-m-d H:i:s");

$query = "
    INSERT INTO hr_chat_messages (sender_id, receiver_id, message, message_type, timestamp)
    VALUES (?, ?, ?, ?, ?)
";

$stmt = $db->prepare($query);
$stmt->bind_param("sssss", $sender_id, $receiver_id, $message, $type, $timestamp);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send']);
}
?>
