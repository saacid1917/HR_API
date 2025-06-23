<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

include('dbconnection.php');

try {
    $con = dbconnection();
    session_start();
    
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('H:i:00');
    $currentDate = date('Y-m-d');
    
    // Initialize or increment counter
    if (!isset($_SESSION['qr_counter'])) {
        $_SESSION['qr_counter'] = 0;
    }
    
    $sql = "SELECT 
            timeID,
            TIME_FORMAT(checkIn, '%H:%i:00') as checkIn,
            TIME_FORMAT(checkOut, '%H:%i:00') as checkOut,
            qrCode
            FROM company_time 
            WHERE DATE(checkIn) = ?
            ORDER BY timeID DESC LIMIT 1";
            
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $checkIn = $row['checkIn'];
        $checkOut = $row['checkOut'];
        $baseQR = $row['qrCode'];

        if ($currentTime >= $checkIn && $currentTime <= $checkOut) {
            // Generate sequential QR
            $newQR = $baseQR . $_SESSION['qr_counter'];
            $_SESSION['qr_counter']++;
            
            echo json_encode([
                'status' => 'active',
                'qrcode' => $newQR,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut
            ]);
        } else if ($currentTime > $checkOut) {
            $_SESSION['qr_counter'] = 0; // Reset counter
            session_destroy();
            echo json_encode([
                'status' => 'closed',
                'message' => 'Office hours ended',
                'checkOut' => $checkOut
            ]);
        } else {
            echo json_encode([
                'status' => 'waiting',
                'checkIn' => $checkIn,
                'checkOut' => $checkOut
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($con)) $con->close();
}
?>