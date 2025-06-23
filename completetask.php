<?php
include("dbconnection.php");

// CORS and content-type headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$con = dbconnection();
if (!$con) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Validate required fields
if (!isset($_POST['taskID']) || !isset($_POST['completeTime']) || !isset($_FILES['file'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields (taskID, completeTime, file)"]);
    exit;
}

$taskID = mysqli_real_escape_string($con, $_POST['taskID']);
$completeTime = mysqli_real_escape_string($con, $_POST['completeTime']);
$completionDocument = isset($_POST['completionDocument']) ? mysqli_real_escape_string($con, $_POST['completionDocument']) : '';
$status = "completed";

// Handle file upload
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'];
$fileName = time() . '_' . basename($file['name']);
$targetFilePath = $uploadDir . $fileName;
$fileType = mime_content_type($file['tmp_name']);

if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    echo json_encode(["success" => false, "message" => "File upload failed"]);
    exit;
}

// SQL: Update task with completion info and file details
$sql = "UPDATE task 
        SET status = ?, 
            completeTime = ?, 
            submissionFile = ?, 
            fileType = ?, 
            completionDocument = ?
        WHERE taskID = ?";

$stmt = mysqli_prepare($con, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssss", $status, $completeTime, $fileName, $fileType, $completionDocument, $taskID);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Task marked as completed with document"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update task: " . mysqli_error($con)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "SQL preparation failed: " . mysqli_error($con)]);
}

mysqli_close($con);
?>
