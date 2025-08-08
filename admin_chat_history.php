<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$employeeId = $_GET['employee_id'] ?? '';

if (empty($employeeId)) {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT 
        m.sender_id,
        m.receiver_id,
        m.message,
        m.message_type,
        m.timestamp,
        CASE 
            WHEN m.sender_id = 'admin' THEN 'admin'
            ELSE 'employee'
        END AS is_sender
    FROM hr_chat_messages m
    WHERE 
        (m.sender_id = 'admin' AND m.receiver_id = ?)
        OR (m.sender_id = ? AND m.receiver_id = 'admin')
    ORDER BY m.timestamp ASC
";

$stmt = $db->prepare($query);
$stmt->bind_param("ss", $employeeId, $employeeId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
