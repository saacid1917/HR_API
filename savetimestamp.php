<?php
include("dbconnection.php");

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required parameters
if (!isset($data['userID']) || !isset($data['timestamp']) || !isset($data['type'])) {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit();
}

$userID = $data['userID'];
$timestamp = $data['timestamp'];
$type = strtolower($data['type']);
$date = date('Y-m-d', strtotime($timestamp));

// Check if record exists for user on this date
$sql_check = "SELECT * FROM employee_timestamp WHERE userID = ? AND DATE(createdAt) = ?";
$stmt_check = $db->prepare($sql_check);
$stmt_check->bind_param("ss", $userID, $date);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // Update existing record
    if ($type === 'checkin') {
        $sql_update = "UPDATE employee_timestamp 
                       SET checkIn = ?, status = 'present', updatedAt = CURRENT_TIMESTAMP 
                       WHERE userID = ? AND DATE(createdAt) = ?";
    } elseif ($type === 'checkout') {
        $sql_update = "UPDATE employee_timestamp 
                       SET checkOut = ?, updatedAt = CURRENT_TIMESTAMP 
                       WHERE userID = ? AND DATE(createdAt) = ?";
    } else {
        echo json_encode(["success" => false, "message" => "Invalid type. Use 'checkIn' or 'checkOut'"]);
        exit();
    }

    $stmt_update = $db->prepare($sql_update);
    $stmt_update->bind_param("sss", $timestamp, $userID, $date);
    $success = $stmt_update->execute();
    $stmt_update->close();

    if ($success && $type === 'checkout') {
        updateAttendancePoints($db, $userID);  // Update points after successful checkout
    }

    $response = $success ? 
        ["success" => true, "message" => "Record updated successfully"] :
        ["success" => false, "message" => "Error updating record"];
    
} else {
    // Insert new record
    if ($type === 'checkin') {
        $sql_insert = "INSERT INTO employee_timestamp (userID, checkIn, status) VALUES (?, ?, 'present')";
    } elseif ($type === 'checkout') {
        $sql_insert = "INSERT INTO employee_timestamp (userID, checkOut, status) VALUES (?, ?, 'present')";
    } else {
        echo json_encode(["success" => false, "message" => "Invalid type. Use 'checkIn' or 'checkOut'"]);
        exit();
    }

    $stmt_insert = $db->prepare($sql_insert);
    $stmt_insert->bind_param("ss", $userID, $timestamp);
    $success = $stmt_insert->execute();
    $stmt_insert->close();

    if ($success && $type === 'checkout') {
        updateAttendancePoints($db, $userID);  // Update points after successful checkout
    }

    $response = $success ? 
        ["success" => true, "message" => "Record inserted successfully"] :
        ["success" => false, "message" => "Error inserting record"];
}

$stmt_check->close();
$db->close();
echo json_encode($response);


/**
 * Updates attendancePoints for the user in the points table.
 */
function updateAttendancePoints($db, $userID) {
    // Check current points
    $sql_points = "SELECT attendancePoints FROM points WHERE userID = ?";
    $stmt_points = $db->prepare($sql_points);
    $stmt_points->bind_param("s", $userID);
    $stmt_points->execute();
    $result = $stmt_points->get_result();

    if ($result->num_rows > 0) {
        // User exists, increment attendancePoints
        $row = $result->fetch_assoc();
        $newPoints = $row['attendancePoints'] + 10;

        $sql_update_points = "UPDATE points SET attendancePoints = ? WHERE userID = ?";
        $stmt_update_points = $db->prepare($sql_update_points);
        $stmt_update_points->bind_param("is", $newPoints, $userID);
        $stmt_update_points->execute();
        $stmt_update_points->close();
    } else {
        // No record in points table, insert a new one with attendancePoints = 1
        $sql_insert_points = "INSERT INTO points (userID, attendancePoints) VALUES (?, 1)";
        $stmt_insert_points = $db->prepare($sql_insert_points);
        $stmt_insert_points->bind_param("s", $userID);
        $stmt_insert_points->execute();
        $stmt_insert_points->close();
    }

    $stmt_points->close();
}
?>
