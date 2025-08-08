<?php
// Top-level imports (must be outside if blocks!)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (
        isset($data['applicant_id']) &&
        isset($data['first_name']) &&
        isset($data['last_name']) &&
        isset($data['email']) &&
        isset($data['phone']) &&
        isset($data['gender']) &&
        isset($data['dob']) &&
        isset($data['address']) &&
        isset($data['experience']) &&
        isset($data['skills']) &&
        isset($data['expected_salary']) &&
        isset($data['decision_status'])
    ) {
        include("dbconnection.php");
        $con = dbconnection();

        // -----------------------------------------
        // ✅ Check if this applicant already exists
        // -----------------------------------------
        $checkSql = "SELECT decision_id FROM applicant_decisions WHERE applicant_id = ?";
        $checkStmt = mysqli_prepare($con, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "i", $data['applicant_id']);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            // Already exists — send error
            echo json_encode([
                "success" => false,
                "message" => "This person already has a decision record."
            ]);
            mysqli_stmt_close($checkStmt);
            mysqli_close($con);
            exit;
        }
        mysqli_stmt_close($checkStmt);

        // -----------------------------------------
        // ✅ Insert the new decision
        // -----------------------------------------
        $stmt = mysqli_prepare($con, "
            INSERT INTO applicant_decisions 
                (applicant_id, first_name, last_name, email, phone, gender, dob, address, experience, skills, expected_salary, decision_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "isssssssssss",
            $data['applicant_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['gender'],
            $data['dob'],
            $data['address'],
            $data['experience'],
            $data['skills'],
            $data['expected_salary'],
            $data['decision_status']
        );

        if (mysqli_stmt_execute($stmt)) {

            $email = $data['email'];
            $firstName = $data['first_name'];
            $decision = $data['decision_status'];

            // Prepare subject + body
            $subject = "";
            $body = "";

            if ($decision === "Accepted") {
                $subject = "Congratulations - You've been accepted!";
                $body = "
                    <h3>Hello {$firstName},</h3>
                    <p>We are thrilled to inform you that you have been <strong>accepted</strong> for the position at Smart HR!</p>
                    <p>We’ll reach out to you with further onboarding steps.</p>
                    <p>Welcome aboard!</p>
                    <p>— Smart HR Team</p>
                ";
            } else if ($decision === "Rejected") {
                $subject = "Update on your application";
                $body = "
                    <h3>Hello {$firstName},</h3>
                    <p>Thank you for your interest in joining Smart HR. We regret to inform you that we have decided not to move forward with your application at this time.</p>
                    <p>We encourage you to apply for future openings that match your profile.</p>
                    <p>— Smart HR Team</p>
                ";
            } else {
                $subject = "Update on your application";
                $body = "
                    <h3>Hello {$firstName},</h3>
                    <p>We’ve updated the status of your application. Your new status is: {$decision}.</p>
                    <p>— Smart HR Team</p>
                ";
            }

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'saacidfaarah4@gmail.com';
                $mail->Password = 'pksq juji vsxd nipz'; // your app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('saacidfaarah4@gmail.com', 'SMART HR');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;

                $mail->send();

                echo json_encode([
                    "success" => true,
                    "message" => "Decision saved and email sent successfully"
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "message" => "Decision saved, but email failed: " . $mail->ErrorInfo
                ]);
            }
        } else {
            echo json_encode(["success" => false, "message" => mysqli_stmt_error($stmt)]);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } else {
        echo json_encode(["success" => false, "message" => "Missing parameters"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
