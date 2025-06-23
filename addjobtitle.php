<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if all required parameters are present
    if (isset($input['name']) && isset($input['department'])) {
        $jobtitle = htmlspecialchars($input['name']);
        $department = htmlspecialchars($input['department']);

        include("dbconnection.php");
        $con = dbconnection();

        // Fetch the department ID from the departments table
        $dept_query = "SELECT deptID FROM dept WHERE deptName = ?";
        if ($dept_stmt = mysqli_prepare($con, $dept_query)) {
            mysqli_stmt_bind_param($dept_stmt, "s", $department);
            mysqli_stmt_execute($dept_stmt);
            mysqli_stmt_bind_result($dept_stmt, $dept_id);
            mysqli_stmt_fetch($dept_stmt);
            mysqli_stmt_close($dept_stmt);
        }

        if (isset($dept_id)) {
            // Insert the job title and department ID into the jobtitle table
            $query = "INSERT INTO jobtitle (title, departmentID ) VALUES (?, ?)";
            if ($stmt = mysqli_prepare($con, $query)) {
                mysqli_stmt_bind_param($stmt, "si", $jobtitle, $dept_id);
                if (mysqli_stmt_execute($stmt)) {
                    $response = array("success" => true, "message" => "Job Title added successfully");
                } else {
                    $response = array("success" => false, "message" => "Failed to add job title: " . mysqli_error($con));
                }
                mysqli_stmt_close($stmt);
            } else {
                $response = array("success" => false, "message" => "Failed to prepare statement: " . mysqli_error($con));
            }
        } else {
            $response = array("success" => false, "message" => "Department not found");
        }
        mysqli_close($con);
    } else {
        $response = array("success" => false, "message" => "Missing required parameters");
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method: POST required");
}

header('Content-Type: application/json');
echo json_encode($response);
?>
