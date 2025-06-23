<?php
include("dbconnection.php"); // your DB connection file

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Validate input
if (!isset($_GET['userID'])) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

$userID = $_GET['userID'];

// Use $db instead of $conn
$query = "SELECT face_image FROM employee WHERE userID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['face_image'])) {
        echo json_encode([
            "success" => true,
            "face_image" => $row['face_image']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No face image found for this user"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
}

$stmt->close();
$db->close();
?>
