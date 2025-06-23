<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("dbconnection.php");

$input = json_decode(file_get_contents("php://input"), true);
$userID = $input['userID'] ?? null;
$date = $input['date'] ?? null;

if ($userID && $date) {
    $con = dbconnection();
    
    $query = "SELECT checkIn, checkOut FROM attendance 
              WHERE userID = ? AND DATE(date) = ?";
              
    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "ss", $userID, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $response = [
                'success' => true,
                'hasCheckIn' => !is_null($row['checkIn']),
                'hasCheckOut' => !is_null($row['checkOut']),
                'checkInTime' => $row['checkIn'],
                'checkOutTime' => $row['checkOut']
            ];
        } else {
            $response = [
                'success' => true,
                'hasCheckIn' => false,
                'hasCheckOut' => false
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Query preparation failed'
        ];
    }
    
    mysqli_close($con);
} else {
    $response = [
        'success' => false,
        'message' => 'Missing required parameters'
    ];
}

echo json_encode($response);
?>