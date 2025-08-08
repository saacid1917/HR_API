<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$messageId = $_POST['message_id'];
$userId    = $_POST['user_id'];

if (empty($messageId) || empty($userId)) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$updateQuery = "UPDATE hr_chat_messages 
                SET deleted_for = IFNULL(CONCAT_WS(',', deleted_for, ?) , ?) 
                WHERE id = ?"; // ✅ Fixed here

$stmt = $db->prepare($updateQuery);
$stmt->bind_param("ssi", $userId, $userId, $messageId);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Message deleted for you" : "Deletion failed"
]);
?>
