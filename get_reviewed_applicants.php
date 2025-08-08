<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("dbconnection.php");
$con = dbconnection();

$sql = "
    SELECT 
        applicant_id,
        first_name,
        last_name,
        email,
        phone,
        gender,
        dob,
        address,
        experience,
        skills,
        expected_salary,
        decision_status
    FROM applicant_decisions
";

$result = mysqli_query($con, $sql);

$applicants = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['applicant_id'] = (int)$row['applicant_id'];
        $applicants[] = $row;
    }

    echo json_encode([
        "success" => true,
        "applicants" => $applicants
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No reviewed applicants found."
    ]);
}

mysqli_close($con);
?>
