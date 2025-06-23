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

// Verify if $conn is set
if (!isset($conn) || $conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . ($conn->connect_error ?? "Unknown error")]));
}

// Fetch salary records
$sql = "SELECT * FROM salary_record";
$result = $conn->query($sql);

if ($result === false) {
    die(json_encode(["status" => "error", "message" => "Query failed: " . $conn->error]));
}

if ($result->num_rows > 0) {
    $salary_records = [];
    while ($row = $result->fetch_assoc()) {
        $salary_records[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $salary_records]);
} else {
    echo json_encode(["status" => "error", "message" => "No records found"]);
}

$conn->close();
?>
