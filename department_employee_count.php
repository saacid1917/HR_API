<?php
include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Fetch department-wise employee count, excluding fired ones
$query = "
SELECT d.deptName, COUNT(e.userID) AS count 
FROM employee e 
JOIN dept d ON e.deptID = d.deptID 
LEFT JOIN fired_employees f ON e.userID = f.userID AND f.status = 'Fired'
WHERE f.userID IS NULL
GROUP BY d.deptName";

$result = $db->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$db->close();
