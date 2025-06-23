<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once('dbconnection.php');
$con = dbconnection();

// Get JSON body data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input (excluding cashout_points)
if (
    isset($data['userID']) &&
    isset($data['basic_salary']) &&
    isset($data['bonus']) &&
    isset($data['deduction']) &&
    isset($data['total_salary']) &&
    isset($data['record_date'])
) {
    $userID = mysqli_real_escape_string($con, $data['userID']);
    $basic_salary = mysqli_real_escape_string($con, $data['basic_salary']);
    $bonus = mysqli_real_escape_string($con, $data['bonus']);
    $deduction = mysqli_real_escape_string($con, $data['deduction']);
    $total_salary = mysqli_real_escape_string($con, $data['total_salary']);
    $record_date = mysqli_real_escape_string($con, $data['record_date']);

    // ✅ Fetch cashout_points from points table
    $cashout_points = 0;
    $result = mysqli_query($con, "SELECT cashout FROM points WHERE userID = '$userID'");
    if ($row = mysqli_fetch_assoc($result)) {
        $cashout_points = (int)$row['cashout'];
    }

    // Insert salary record
    $sql = "INSERT INTO salary_record 
            (userID, basic_salary, bonus, deduction, cashout_points, total_salary, record_date) 
            VALUES 
            ('$userID', '$basic_salary', '$bonus', '$deduction', '$cashout_points', '$total_salary', '$record_date')";

    if (mysqli_query($con, $sql)) {
        echo json_encode(["success" => true, "message" => "Salary inserted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed: " . mysqli_error($con)]);
    }

    mysqli_free_result($result);
} else {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
}

mysqli_close($con);
?>
