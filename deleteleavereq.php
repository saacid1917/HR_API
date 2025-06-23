<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include("dbconnection.php");

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['leaveID'])) {
    $leaveID = $data['leaveID'];
    $conn = dbconnection();
    
    $query = "DELETE FROM leavetable WHERE leaveID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $leaveID);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Leave request deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete leave request']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
}
?>