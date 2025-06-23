<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Retrieve JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validate input data
if (!isset($data['employeeId'])) {
    echo json_encode(["error" => "Missing employeeId parameter"]);
    exit;
}

$employeeId = intval($data['employeeId']);
$cashout = 0; // Set cashout to 0 by default

// Update cashout value
$sql = "UPDATE points SET cashout = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cashout, $employeeId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Cashout 0 updated successfully"]);
    } else {
        echo json_encode(["error" => "No employee found with ID: " . $employeeId]);
    }
} else {
    echo json_encode(["error" => "Failed to update cashout: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>