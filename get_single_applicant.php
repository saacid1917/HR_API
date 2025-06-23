<?php
// === DEBUG MODE ON ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// === DB CONNECTION ===
include("dbconnection.php");
$con = dbconnection();

if (!$con) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => mysqli_connect_error()
    ]);
    exit;
}

// === GET applicant_id FROM URL ===
if (!isset($_GET['applicant_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing applicant_id parameter"
    ]);
    exit;
}

$applicant_id = intval($_GET['applicant_id']);

// === FETCH SINGLE APPLICANT DETAILS ===
$sql = "SELECT id, first_name, last_name, email, phone, gender, experience, expected_salary, skills, cv_filename 
        FROM job_applications 
        WHERE id = ? LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $applicant_id);

if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $applicant = mysqli_fetch_assoc($result);
        $applicant['id'] = (int)$applicant['id'];

        echo json_encode([
            "success" => true,
            "applicant" => $applicant
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Applicant not found"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Query execution failed",
        "error" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
?>
