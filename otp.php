<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoload

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json'); // Ensure JSON response

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    // Read JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate JSON input
    if (!$data) {
        echo json_encode(["success" => false, "error" => "Invalid JSON or empty request body"]);
        exit;
    }

    if (!isset($data['email']) || empty($data['email'])) {
        echo json_encode(["success" => false, "error" => "Email field is required"]);
        exit;
    }

    $recipientEmail = filter_var($data['email'], FILTER_VALIDATE_EMAIL);

    if (!$recipientEmail) {
        echo json_encode(["success" => false, "error" => "Invalid email address"]);
        exit;
    }

    // Generate a 6-digit OTP
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'saacidfaarah4@gmail.com'; 
    $mail->Password = 'pksq juji vsxd nipz'; // Replace with correct credentials
    $mail->setFrom('saacidfaarah4@gmail.com', 'SMART HR');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Set email headers
    $mail->addAddress($recipientEmail);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Your One-Time Password (OTP)';

    $mail->Body = "
    <div style='font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; border-radius: 10px; border: 1px solid #ddd; max-width: 600px; margin: auto;'>
        <div style='text-align: center; margin-bottom: 20px;'>
            <h1 style='font-size: 28px; color: #ff5722; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 2px;'>
                SMART Mobile Application For Human Resource Management With Facial Recognition Features For Employees
        
            </h1>
            <img src='https://i.imgur.com/OAp1xTt.png' alt='SIMAD logo' style='max-width: 150px; margin-top: 10px;'>
        </div>
        <h2 style='color: #333; text-align: center; font-size: 24px; margin-top: 20px;'>Your One-Time Password (OTP)</h2>
        <p style='color: #666; text-align: center; font-size: 16px;'>
            To complete your process, please use the OTP provided below. This code is valid for <strong>10 minutes</strong>.
        </p>
        <div style='text-align: center; margin: 30px 0;'>
            <span style='
                display: inline-block;
                font-size: 30px;
                font-weight: bold;
                color: #ffffff;
                background: linear-gradient(90deg, #007BFF, #0056b3);
                padding: 15px 30px;
                border-radius: 10px;
                animation: pulse 1.5s infinite;
                text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);'>
                $otp
            </span>
        </div>
        <p style='color: #666; text-align: center; font-size: 14px;'>
            Copy this number to recover your password.
        </p>
        <div style='text-align: center; margin-top: 20px;'>
            <p style='color: #333; font-size: 16px; font-weight: bold; margin-bottom: 5px;'>Thank you,</p>
            <p style='color: #333; font-size: 16px; margin: 0;'>The TGI Team</p>
        </div>
        <footer style='margin-top: 30px; text-align: center; font-size: 12px; color: #999;'>
            &copy; " . date('Y') . " Smart HR .
        </footer>
    </div>
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        span:hover {
            background: linear-gradient(90deg, #0056b3, #007BFF);
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.5);
        }
    </style>
    ";

    // Send email
    if (!$mail->send()) {
        echo json_encode(["success" => false, "error" => "Mailer Error: " . $mail->ErrorInfo]);
        exit;
    }

    // Response
    echo json_encode([
        "success" => true,
        "message" => "OTP sent successfully",
        "otp" => $otp
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
    exit;
}
?>
