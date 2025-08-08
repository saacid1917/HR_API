<?php
error_reporting(0);
ini_set('display_errors', 0);

include("dbconnection.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Check if file is being uploaded
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Define the upload directory and file name
    $uploadDir = "uploads/chat_files/";  // You can change this directory as per your structure
    $fileName = uniqid() . "_" . basename($file['name']);
    $targetFilePath = $uploadDir . $fileName;
    
    // Check if the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);  // Create the directory if it doesn't exist
    }
    
    // Try to move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Return the file path as a response
        echo json_encode(["success" => true, "filePath" => $targetFilePath]);
    } else {
        echo json_encode(["success" => false, "message" => "File upload failed"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No file uploaded"]);
}
?>
