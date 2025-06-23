<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'dbconnection.php';
$con = dbconnection();

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? '';

if (empty($id)) {
    echo json_encode(["success" => false, "message" => "Missing notice ID"]);
    exit;
}

$sql = "DELETE FROM notices WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Notice deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Deletion failed"]);
}

$stmt->close();
$con->close();
?>
