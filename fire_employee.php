<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'dbconnection.php';
$con = dbconnection();

$input = json_decode(file_get_contents("php://input"), true);

$userID = $input['userID'] ?? '';
$department = $input['department'] ?? '';
$firingDate = $input['firing_date'] ?? date("Y-m-d");
$reason = $input['reason'] ?? '';
$yearsWorked = $input['years_worked'] ?? '';
$status = "fired";

if (empty($userID) || empty($department) || empty($reason) || empty($yearsWorked)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Insert into fired_employees
$stmt = $con->prepare("INSERT INTO fired_employees (userID, department, firing_date, reason, years_worked, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $userID, $department, $firingDate, $reason, $yearsWorked, $status);

if ($stmt->execute()) {
    // Optionally update user's status in users_table
    $con->query("UPDATE users_table SET role = 'Fired' WHERE userID = $userID");

    echo json_encode(["success" => true, "message" => "Employee fired successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error firing employee: " . $stmt->error]);
}

$stmt->close();
$con->close();
?>
