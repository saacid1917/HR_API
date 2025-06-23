<?php
// employee_birthdays.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable CORS (for cross-origin requests if needed)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Include database connection file
include("dbconnection.php");
$con = dbconnection();

if (!$con) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

$today_month = date('m');
$today_day = date('d');

$sql = "SELECT userID, firstName, birthdate FROM employee";
$result = mysqli_query($con, $sql);

if (!$result) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Query failed: " . mysqli_error($con)]);
    mysqli_close($con);
    exit;
}

$birthdays_employees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $birthdate = $row['birthdate'];
    if ($birthdate) {
        $birth_month = date('m', strtotime($birthdate));
        $birth_day = date('d', strtotime($birthdate));

        if ($birth_month == $today_month && $birth_day == $today_day) {
            $birthdays_employees[] = [
                "userID" => $row['userID'],
                "firstName" => $row['firstName'],
                "birthdate" => $birthdate // You can format date if needed
            ];
        }
    }
}

mysqli_free_result($result);
mysqli_close($con);

echo json_encode($birthdays_employees);

?>