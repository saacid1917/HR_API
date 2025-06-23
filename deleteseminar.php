<?php
// Start output buffering to catch any accidental output
ob_start();

// Set headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection
include("dbconnection.php");

try {
    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    if (empty($jsonInput)) {
        throw new Exception('No input data received');
    }

    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($data['seminarID'])) {
        throw new Exception('Seminar ID is required');
    }

    $seminarID = $data['seminarID'];
    $conn = dbconnection();
    
    $query = "DELETE FROM seminar WHERE seminarID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $seminarID);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Seminar deleted successfully'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to delete Seminar: ' . $conn->error];
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// Clean any output and send JSON
ob_end_clean();
echo json_encode($response);
exit;
?>