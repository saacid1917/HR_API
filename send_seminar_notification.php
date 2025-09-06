<?php
header('Content-Type: application/json');
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;  // Import SMTP class
use PHPMailer\PHPMailer\Exception;

// --- Database Connection (Improved) ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr";

// Use try-catch for connection errors
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error); // Throw for consistent error handling
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}

// --- Input Handling (Improved) ---
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['title']) || !isset($data['place']) || !isset($data['date'])) {
    echo json_encode(["success" => false, "message" => "Invalid input: Title, place, and date required."]);
    $conn->close(); // Close connection on error
    exit;
}

$seminarTitle = $data['title'];
$seminarPlace = $data['place'];
$seminarDate  = $data['date'];

// --- Fetch Admin Email (Improved) ---
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

// --- Fetch Employee Emails (Improved) ---
$sqlEmployees = "SELECT email FROM users_table WHERE Role = 'employee'";
try {
    $resultEmployees = $conn->query($sqlEmployees);
    if (!$resultEmployees) {
        throw new Exception("Error fetching employee emails: " . $conn->error); // More specific error
    }

    if ($resultEmployees->num_rows == 0) {
        echo json_encode(["success" => true, "message" => "Seminar added, but no employees to notify."]);
        $conn->close();
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    $conn->close();
    exit;
}

// --- PHPMailer Setup (Improved) ---
$mail = new PHPMailer(true); // 'true' enables exceptions

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to SMTP::DEBUG_SERVER for debugging
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // >>> From first script (Gmail + app password) <<<
    $mail->Username   = 'saacidfaarah4@gmail.com';      // Gmail address
    $mail->Password   = 'pksq juji vsxd nipz';          // Gmail app password

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Use first script branding for the sender
    $mail->setFrom('saacidfaarah4@gmail.com', 'SMART HR');

    // (Optional) You can let replies go to the admin account if you like
    if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($adminEmail, 'Admin');
    }

    // Recipients (using addBCC for efficiency)
    while ($row = $resultEmployees->fetch_assoc()) {
        $to = $row["email"];
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) { // Validate email before adding
            $mail->addBCC($to); // Add as BCC. More efficient for multiple recipients.
        }
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Seminar Announcement: $seminarTitle";

    // >>> First script branding applied: logo + company name <<<
    $companyName = 'SMART HR';
    $logoUrl = 'https://i.imgur.com/OAp1xTt.png';

    $mail->Body    = <<<HTML
    <div style="font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; padding: 30px; border-radius: 12px; border: none; max-width: 640px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,87,34,0.2);">
            <img src="{$logoUrl}" 
                 alt="{$companyName} Logo" 
                 style="max-width: 120px; margin-bottom: 15px; border-radius: 50%; border: 3px solid #ff5722; padding: 5px; background: white; box-shadow: 0 4px 12px rgba(255,87,34,0.2);">
            <h1 style="font-size: 26px; color: #2c3e50; font-weight: 600; margin: 0; letter-spacing: 1px;">
                {$companyName}
                <div style="width: 80px; height: 3px; background: linear-gradient(90deg, #ff5722, #ff9800); margin: 10px auto; border-radius: 3px;"></div>
            </h1>
        </div>
    
        <!-- Event Title -->
        <div style="background: linear-gradient(135deg, rgba(255,87,34,0.1), rgba(255,87,34,0.05)); padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; border-left: 4px solid #ff5722;">
            <div style="font-size: 12px; color: #ff5722; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Featured Event</div>
            <h2 style="color: #2c3e50; font-size: 22px; margin: 0; font-weight: 700; line-height: 1.3;">
                {$seminarTitle}
            </h2>
        </div>
    
        <!-- Event Details Card -->
        <div style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.05); margin: 25px 0; overflow: hidden;">
            <!-- Location Section -->
            <div style="display: flex; align-items: center; padding: 18px 20px; border-bottom: 1px solid #f1f1f1;">
                <span style="font-size: 20px; margin-right: 15px;">📍</span>
                <div>
                    <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Venue</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">{$seminarPlace}</div>
                </div>
            </div>
            
            <!-- Date Section -->
            <div style="display: flex; align-items: center; padding: 18px 20px;">
                <span style="font-size: 20px; margin-right: 15px;">📅</span>
                <div>
                    <div style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px;">Date & Time</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">{$seminarDate}</div>
                </div>
            </div>
        </div>

        <!-- Closing Text -->
        <p style="text-align: center; font-size: 15px; color: #555; line-height: 1.6; font-weight: 500; max-width: 80%; margin: 0 auto 30px;">
            Join industry leaders for this exclusive seminar. Network with peers and gain cutting-edge insights!
        </p>
    
        <!-- Signature -->
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
            <p style="color: #2c3e50; font-size: 15px; font-weight: 600; margin-bottom: 5px;">Best Regards,</p>
            <p style="color: #7f8c8d; font-size: 14px; margin: 0 0 10px;">{$companyName}</p>
            <img src="{$logoUrl}" alt="{$companyName} Signature" style="height: 40px; margin-top: 10px; opacity: 0.8;">
        </div>
    
        <!-- Footer -->
        <footer style="margin-top: 40px; text-align: center; font-size: 12px; color: #95a5a6;">
            © <?php echo date('Y'); ?> {$companyName}. All Rights Reserved.<br>
            <div style="margin-top: 8px;">
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Privacy Policy</a> | 
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Contact Us</a> | 
                <a href="#" style="color: #95a5a6; text-decoration: none; margin: 0 8px;">Unsubscribe</a>
            </div>
        </footer>
    </div>
HTML;

    $mail->send();
    echo json_encode(["success" => true, "message" => "Seminar added and emails sent successfully."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
} finally {
    $conn->close(); // Always close the connection
}
?>
