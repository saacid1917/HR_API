<?php
include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$query = "SELECT gender, COUNT(*) as count FROM employee GROUP BY gender";
$result = mysqli_query($db, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
