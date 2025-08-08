<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Get latest message per employee who messaged admin
$query = "
  SELECT 
    m.sender_id,
    e.firstName,
    e.lastName,
    e.photo,
    m.message,
    m.message_type,
    m.timestamp
  FROM hr_chat_messages m
  JOIN employee e ON m.sender_id = e.userID
  WHERE m.receiver_id = 'admin'
    AND (m.deleted_for IS NULL OR FIND_IN_SET('admin', m.deleted_for) = 0)
    AND m.timestamp = (
        SELECT MAX(m2.timestamp)
        FROM hr_chat_messages m2
        WHERE m2.sender_id = m.sender_id AND m2.receiver_id = 'admin'
    )
  ORDER BY m.timestamp DESC
";

$result = $db->query($query);
$chatUsers = [];

while ($row = $result->fetch_assoc()) {
    $chatUsers[] = [
        "sender_id" => $row['sender_id'],
        "name" => $row['firstName'] . ' ' . $row['lastName'],
        "photo_base64" => $row['photo'] ? base64_encode($row['photo']) : null,
        "message" => $row['message'],
        "message_type" => $row['message_type'],
        "timestamp" => $row['timestamp']
    ];
}

echo json_encode($chatUsers);
?>
