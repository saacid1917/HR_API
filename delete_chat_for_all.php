<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$messageId = $_POST['message_id'];

if (empty($messageId)) {
    echo json_encode(["success" => false, "message" => "Missing message_id"]);
    exit;
}

$query = "DELETE FROM hr_chat_messages WHERE id = ?"; // ✅ Fixed here
$stmt = $db->prepare($query);
$stmt->bind_param("i", $messageId);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Message deleted for all" : "Delete failed"
]);
?>
