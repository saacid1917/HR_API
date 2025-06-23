<?php
header('Content-Type: application/json');
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

// Input Handling
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['title']) || !isset($data['place']) || !isset($data['date'])) {
    echo json_encode(["success" => false, "message" => "Invalid input: Title, place, and date required."]);
    $conn->close();
    exit;
}

$seminarTitle = $data['title'];
$seminarPlace = $data['place'];
$seminarDate = $data['date'];

// Fetch Admin Email
$sqlAdmin = "SELECT email FROM users_table WHERE Role = 'admin' LIMIT 1";
try {
    $resultAdmin = $conn->query($sqlAdmin);
    if ($resultAdmin && $resultAdmin->num_rows > 0) {
        $adminEmail = $resultAdmin->fetch_assoc()['email'];
    } else {
        throw new Exception("No admin email found.");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    $conn->close();
    exit;
}

// Fetch Employee Emails
$sqlEmployees = "SELECT email FROM users_table WHERE Role = 'employee'";
try {
    $resultEmployees = $conn->query($sqlEmployees);
    if (!$resultEmployees) {
        throw new Exception("Error fetching employee emails: " . $conn->error);
    }

    if ($resultEmployees->num_rows == 0) {
         echo json_encode(["success" => true, "message" => "Seminar canceled, but no employees to notify."]);
         $conn->close();
         exit;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    $conn->close();
    exit;
}

// PHPMailer Setup
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'technoguideinfosoft.hr@gmail.com';
    $mail->Password   = 'abbz bowk ufpr zhkj';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom($adminEmail, 'HR Department');

    // Recipients
    while ($row = $resultEmployees->fetch_assoc()) {
        $to = $row["email"];
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $mail->addBCC($to);
        }
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Seminar Cancellation: $seminarTitle";
    $mail->Body    = <<<HTML
    <div style="font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; padding: 30px; border-radius: 12px; border: none; max-width: 640px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,87,34,0.2);">
            <img src="https://yt3.googleusercontent.com/ytc/AGIKgqNg7PjgzPar-A-1uZEMwqQgKcQIge1NNu80K1-wYQ=s900-c-k-c0x00ffffff-no-rj" 
                 alt="TGI Logo" 
                 style="max-width: 120px; margin-bottom: 15px; border-radius: 50%; border: 3px solid #ff5722; padding: 5px; background: white; box-shadow: 0 4px 12px rgba(255,87,34,0.2);">
            <h1 style="font-size: 26px; color: #2c3e50; font-weight: 600; margin: 0; letter-spacing: 1px;">
                Techno Guide Infosoft Pvt Ltd
                <div style="width: 80px; height: 3px; background: linear-gradient(90deg, #ff5722, #ff9800); margin: 10px auto; border-radius: 3px;"></div>
            </h1>
        </div>
    
        <!-- Cancellation Notice -->
        <div style="background: linear-gradient(135deg, rgba(255,87,34,0.1), rgba(255,87,34,0.05)); padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; border-left: 4px solid #ff5722;">
            <div style="font-size: 12px; color: #ff5722; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Important Notice</div>
            <h2 style="color: #2c3e50; font-size: 22px; margin: 0; font-weight: 700; line-height: 1.3;">
                Seminar Cancellation
            </h2>
        </div>
    
        <!-- Event Details Card -->
        <div style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.05); margin: 25px 0; overflow: hidden;">
            <!-- Title Section -->
            <div style="display: flex; align-items: center; padding: 18px 20px; border-bottom: 1px solid #f1f1f1;">
                <span style="color: #dc3545; font-size: 20px; margin-right: 15px;">❌</span>
                <div>
                    <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Canceled Seminar</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">$seminarTitle</div>
                </div>
            </div>
            
            <!-- Location Section -->
            <div style="display: flex; align-items: center; padding: 18px 20px; border-bottom: 1px solid #f1f1f1;">
                <span style="color: #007BFF; font-size: 20px; margin-right: 15px;">📍</span>
                <div>
                    <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Originally Scheduled Venue</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">$seminarPlace</div>
                </div>
            </div>
            
            <!-- Date Section -->
            <div style="display: flex; align-items: center; padding: 18px 20px;">
                <span style="color: #28a745; font-size: 20px; margin-right: 15px;">📅</span>
                <div>
                    <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Originally Scheduled Date</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">$seminarDate</div>
                </div>
            </div>
        </div>
        
        <!-- Cancellation Message -->
        <div style="background: #fff3f3; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #dc3545;">
            <h3 style="color: #dc3545; font-size: 18px; margin-top: 0; margin-bottom: 10px;">We regret to inform you</h3>
            <p style="color: #2c3e50; font-size: 15px; line-height: 1.6; margin-bottom: 0;">
                The seminar mentioned above has been canceled due to unforeseen circumstances. We apologize for any inconvenience this may cause.
            </p>
        </div>
    
        <!-- Signature -->
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
            <p style="color: #2c3e50; font-size: 15px; font-weight: 600; margin-bottom: 5px;">Best Regards,</p>
            <p style="color: #7f8c8d; font-size: 14px; margin: 0 0 10px;">HR Department</p>
            <img src="https://via.placeholder.com/120x40?text=TGI+Signature" alt="Company Signature" style="height: 40px; margin-top: 10px; opacity: 0.8;">
        </div>
    
        <!-- Footer -->
        <footer style="margin-top: 40px; text-align: center; font-size: 12px; color: #95a5a6;">
            © 2023 Techno Guide Infosoft Pvt Ltd. All Rights Reserved.<br>
            <div style="margin-top: 8px;">
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Privacy Policy</a> | 
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Contact Us</a> | 
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Unsubscribe</a>
            </div>
        </footer>
    </div>
HTML;

    $mail->send();
    echo json_encode(["success" => true, "message" => "Cancellation emails sent successfully."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
} finally {
    $conn->close();
}
?>