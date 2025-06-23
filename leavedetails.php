<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');
try {
    include("dbconnection.php");

    // Get the database connection
    $db = dbconnection();

    if (!$db) {
        echo json_encode([
            "success" => false,
            "message" => "Database connection failed: " . mysqli_connect_error()
        ]);
        exit;
    }
    // Execute the query
    $query = "SELECT l.*, e.firstName 
          FROM leavetable l
          JOIN employee e ON l.userID = e.userID";

    $result = $db->query($query);

    if (!$result) {
        throw new Exception("Query execution failed: " . $db->error);
    }

    // Check if there are any records
    if ($result->num_rows > 0) {
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode($records);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No records found"
        ]);
    }

    $db->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>
