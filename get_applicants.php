<?php
// === DEBUG MODE ON ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// === DB CONNECTION ===
include("dbconnection.php");
$con = dbconnection();

if (!$con) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => mysqli_connect_error()
    ]));
}

// === GET JOB ID FROM URL ===
if (isset($_GET['job_id'])) {
    $job_id = intval($_GET['job_id']);

    // ✅ UPDATED QUERY:
    // Only select applicants who do not already have a decision
    $sql = "
        SELECT 
            ja.id, 
            ja.first_name, 
            ja.last_name, 
            ja.gender, 
            ja.email, 
            ja.phone, 
            ja.experience, 
            ja.expected_salary, 
            ja.skills, 
            ja.cv_filename
        FROM job_applications ja
        LEFT JOIN applicant_decisions ad
            ON ja.id = ad.applicant_id
        WHERE ja.job_id = ?
          AND ad.applicant_id IS NULL
    ";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $job_id);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $rawApplicants = [];

            while ($row = mysqli_fetch_assoc($result)) {
                $rawApplicants[] = $row;
            }

            // Force 'id' to integer
            $applicants = array_map(function ($item) {
                $item['id'] = (int)$item['id'];
                return $item;
            }, $rawApplicants);

            echo json_encode([
                "success" => true,
                "applicants" => $applicants
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No undecided applicants found for this job"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database query failed",
            "sql_error" => mysqli_stmt_error($stmt)
        ]);
    }

    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Missing job_id parameter"
    ]);
}

mysqli_close($con);
?>
