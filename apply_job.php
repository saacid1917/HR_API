<?php
// === DEBUG MODE ON ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// === DB CONNECTION ===
include("dbconnection.php");
$con = dbconnection();
if (!$con) {
    die(json_encode(["success" => false, "message" => "Database connection failed", "error" => mysqli_connect_error()]));
}

// === REQUIRED FIELDS CHECK ===
if (
    isset($_POST['job_id']) &&
    isset($_POST['first_name']) &&
    isset($_POST['last_name']) &&
    isset($_POST['dob']) &&
    isset($_POST['gender']) &&
    isset($_POST['phone']) &&
    isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['experience']) &&
    isset($_POST['expected_salary']) &&
    isset($_POST['skills']) && // ✅ NEW FIELD
    isset($_FILES['cv_file'])
) {
    // === SANITIZE INPUTS ===
    $job_id = $_POST['job_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $experience = $_POST['experience'];
    $expected_salary = $_POST['expected_salary'];
    $skills = $_POST['skills']; // ✅ NEW VARIABLE

    // === HANDLE FILE UPLOAD ===
    $target_dir = "uploads/cv/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = uniqid() . "_" . basename($_FILES["cv_file"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["cv_file"]["tmp_name"], $target_file)) {
        // === PREPARE SQL ===
        $sql = "INSERT INTO job_applications 
                (job_id, first_name, last_name, dob, gender, phone, email, password, experience, expected_salary, skills, cv_filename) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssssssssss", 
            $job_id, $first_name, $last_name, $dob, $gender, $phone, $email, $password, $experience, $expected_salary, $skills, $filename
        );

        // === EXECUTE SQL ===
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Application submitted successfully"]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Database insertion failed",
                "sql_error" => mysqli_stmt_error($stmt)
            ]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "CV upload failed"]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields",
        "received" => $_POST,
        "file_info" => isset($_FILES['cv_file']) ? $_FILES['cv_file']['name'] : 'No file'
    ]);
}
?>
