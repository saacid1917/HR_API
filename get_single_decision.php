<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("dbconnection.php");
$con = dbconnection();

// Validate input
if (!isset($_GET['applicant_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing applicant_id parameter"
    ]);
    exit;
}

$applicantId = intval($_GET['applicant_id']);

// Query using JOIN to include cv_filename from job_applications
$sql = "
    SELECT 
        ad.applicant_id,
        ad.first_name,
        ad.last_name,
        ad.email,
        ad.phone,
        ad.gender,
        ad.dob,
        ad.address,
        ad.experience,
        ad.skills,
        ad.expected_salary,
        ad.decision_status,
        ja.cv_filename
    FROM applicant_decisions AS ad
    LEFT JOIN job_applications AS ja ON ad.applicant_id = ja.id
    WHERE ad.applicant_id = ?
";

$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare statement"
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $applicantId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $applicant = mysqli_fetch_assoc($result);
    echo json_encode([
        "success" => true,
        "applicant" => $applicant
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Applicant not found."
    ]);
}

mysqli_close($con);
?>
