<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['userID'])) {
    include("dbconnection.php");
    $con = dbconnection();
    
    $userID = htmlspecialchars($data['userID']);
    
    $query = "SELECT DISTINCT seminarID FROM seminar_attendance WHERE userID = ? AND status = 'attended'";
    
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $userID);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $attended_seminars = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Return in a structured format
            echo json_encode([
                'success' => true,
                'data' => $attended_seminars
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch records: ' . mysqli_error($con)
            ]);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($con);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request or missing userID'
    ]);
}
?>