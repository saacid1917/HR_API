<?php
// Turn off PHP error display to avoid HTML output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// === HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

try {
    include("dbconnection.php");
    $con = dbconnection();

    if (!isset($_GET['job_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing job_id parameter"
        ]);
        exit;
    }

    $job_id = intval($_GET['job_id']);

    $sql = "SELECT id, jobTitle AS title, department, salary
        FROM jobs
        WHERE id = ?
        LIMIT 1";

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $job = mysqli_fetch_assoc($result);
        $job['id'] = (int)$job['id'];

        echo json_encode([
            "success" => true,
            "job" => $job
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Job not found"
        ]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);

} catch (Throwable $e) {
    // Ensure JSON error output even for fatal errors
    echo json_encode([
        "success" => false,
        "message" => "Server error occurred.",
        "error" => $e->getMessage()
    ]);
}
?>
