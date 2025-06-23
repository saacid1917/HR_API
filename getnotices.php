<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'dbconnection.php';
$con = dbconnection();

$sql = "SELECT * FROM notices ORDER BY created_at DESC";
$result = $con->query($sql);

$notices = [];
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}

echo json_encode($notices);
?>
