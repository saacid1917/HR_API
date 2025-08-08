<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$senderId     = $_POST['senderId'];
$receiverId   = $_POST['receiverId'];
$message      = $_POST['message'];
$messageType  = $_POST['messageType']; // 'text', 'image', 'file'
$filePath     = isset($_POST['filePath']) ? $_POST['filePath'] : null;

// Validate required fields
if (empty($senderId) || empty($receiverId) || empty($messageType)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Check if messageType is 'image' or 'file' and validate filePath
if (($messageType === 'image' || $messageType === 'file') && empty($filePath)) {
    echo json_encode(["success" => false, "message" => "File path is missing for $messageType"]);
    exit;
}

// Insert message into the database
$query = "INSERT INTO hr_chat_messages (sender_id, receiver_id, message, file_path, message_type) 
          VALUES (?, ?, ?, ?, ?)";

$stmt = $db->prepare($query);
$stmt->bind_param("sssss", $senderId, $receiverId, $message, $filePath, $messageType);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Message sent" : "Message failed"
]);
?>
