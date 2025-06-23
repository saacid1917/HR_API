<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'dbconnection.php';
$con = dbconnection();

$input = json_decode(file_get_contents("php://input"), true);

$department = $input['department'] ?? '';
$joinDate = $input['joinDate'] ?? '';

if (empty($department) || empty($joinDate)) {
    echo json_encode(["success" => false, "message" => "Missing department or joinDate"]);
    exit;
}

$sql = "SELECT * FROM notices 
        WHERE (department = ? OR department = 'All Employees') 
        AND created_at >= ?
        ORDER BY created_at DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $department, $joinDate);
$stmt->execute();
$result = $stmt->get_result();

$notices = [];
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}

echo json_encode($notices);
?>
