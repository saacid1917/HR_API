<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Consider restricting this in production
header('Access-Control-Allow-Methods: GET'); // Specify allowed methods
header('Access-Control-Allow-Headers: Content-Type'); // Allowed headers


$host = "localhost";
$user = "root";
$password = "";
$database = "hr";  // Correct database name

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

$sql = "SELECT leaveID, userID, leaveType, reason, startDate, endDate, status FROM leavetable"; // Corrected SQL
$result = mysqli_query($conn, $sql);

if ($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    // Return data as JSON
    echo json_encode(['data' => $data, 'status' => 'success']);
} else {
    // Handle query error
     echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn), 'status' => 'error']);
}


mysqli_close($conn);

?>