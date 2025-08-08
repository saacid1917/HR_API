<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$employeeId = $_POST['employeeId'];

if (empty($employeeId)) {
    echo json_encode(["success" => false, "message" => "Missing employeeId"]);
    exit;
}

// Fetch chat messages between employee and admin, excluding deleted messages for this user
$query = "SELECT * FROM hr_chat_messages 
          WHERE ((sender_id = ? AND receiver_id = 'admin') 
              OR (sender_id = 'admin' AND receiver_id = ?))
            AND (deleted_for IS NULL OR FIND_IN_SET(?, deleted_for) = 0)
          ORDER BY timestamp ASC";

$stmt = $db->prepare($query);
$stmt->bind_param("sss", $employeeId, $employeeId, $employeeId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // Attach file URL for non-text messages
    if ($row['message_type'] !== 'text') {
        $row['file_url'] = $row['file_path']; // Keep for frontend, if needed
    }
    $messages[] = $row;
}

echo json_encode($messages);
?>
