<?php
header('Content-Type: application/json');

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Fetch input data (JSON or form-data)
$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($input['email']) ? $input['email'] : (isset($_POST['email']) ? $_POST['email'] : null);
    $password = isset($input['password']) ? $input['password'] : (isset($_POST['password']) ? $_POST['password'] : null);

    error_log("Incoming Email: " . $email);
    error_log("Incoming Password: " . $password);

    if (!empty($email) && !empty($password)) {
        include("dbconnection.php");
        $con = dbconnection();

        if (!$con) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection failed"
            ]);
            exit;
        }

        // Sanitize inputs
        $email = mysqli_real_escape_string($con, $email);
        $password = mysqli_real_escape_string($con, $password);

        // Prepare SQL query
        $stmt = $con->prepare("SELECT userid, email, password, role FROM users_table WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Plaintext password verification
                if ($password === $user['password']) {

                    // 🔒 Check if the user is in the fired_employees table
                    $firedStmt = $con->prepare("SELECT * FROM fired_employees WHERE userID = ?");
                    $firedStmt->bind_param("s", $user['userid']);
                    $firedStmt->execute();
                    $firedResult = $firedStmt->get_result();

                    if ($firedResult->num_rows > 0) {
                        // User is fired
                        echo json_encode([
                            "success" => false,
                            "status" => "fired",
                            "message" => "Your account has been deactivated due to termination. Please contact HR."
                        ]);
                    } else {
                        // Normal login
                        echo json_encode([
                            "success" => true,
                            "message" => "Login successful",
                            "userid" => $user['userid'],
                            "email" => $user['email'],
                            "role" => $user['role']
                        ]);
                    }

                    $firedStmt->close();

                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Incorrect password"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Email not found"
                ]);
            }

            $stmt->close();
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to prepare database query"
            ]);
        }

        mysqli_close($con);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing required parameters (email or password)"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Use POST"
    ]);
}
?>