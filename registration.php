<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $requiredParams = [
        'firstname', 'lastname', 'birthdate', 'address', 'netsalary',
        'contactnumber', 'jobtitle', 'departmentname', 'email', 'password', 'role', 'gender'
    ];

    $missingParams = array_values(array_filter($requiredParams, fn($param) => empty($input[$param])));

    if (empty($missingParams)) {
        include("dbconnection.php");
        $con = dbconnection();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Better error reporting
        mysqli_begin_transaction($con);

        try {
            $firstname = trim($input['firstname']);
            $lastname = trim($input['lastname']);
            $birthdate = trim($input['birthdate']);
            $address = trim($input['address']);
            $netsalary = floatval($input['netsalary']);
            $contactnumber = trim($input['contactnumber']);
            $jobtitle = trim($input['jobtitle']);
            $departmentname = trim($input['departmentname']);
            $email = trim($input['email']);
            $password = trim($input['password']); // Securely hash password
            $role = trim($input['role']);
            $gender = trim($input['gender']);

            $allowedGenders = ['Male', 'Female', 'Other'];
            if (!in_array($gender, $allowedGenders)) {
                throw new Exception("Invalid gender value. Allowed values: Male, Female, Other");
            }

            // Check if email already exists
            $checkEmailQuery = "SELECT userID FROM users_table WHERE email = ?";
            $checkStmt = mysqli_prepare($con, $checkEmailQuery);
            mysqli_stmt_bind_param($checkStmt, "s", $email);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                throw new Exception("Email already exists.");
            }
            mysqli_stmt_close($checkStmt);

            // Insert into users_table
            $usersQuery = "INSERT INTO users_table (email, password, role) VALUES (?, ?, ?)";
            $usersStmt = mysqli_prepare($con, $usersQuery);
            mysqli_stmt_bind_param($usersStmt, "sss", $email, $password, $role);
            mysqli_stmt_execute($usersStmt);
            $userID = mysqli_insert_id($con);

            // Fetch deptID
            $deptQuery = "SELECT deptID FROM dept WHERE deptName = ?";
            $deptStmt = mysqli_prepare($con, $deptQuery);
            mysqli_stmt_bind_param($deptStmt, "s", $departmentname);
            mysqli_stmt_execute($deptStmt);
            $result = mysqli_stmt_get_result($deptStmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $deptID = $row['deptID'];
            } else {
                throw new Exception("Invalid department name: $departmentname");
            }

            // Insert into employee_table
            $employeeQuery = "INSERT INTO employee (userID, firstName, lastName, birthdate, address, netSalary, contactNumber, deptID, gender) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $employeeStmt = mysqli_prepare($con, $employeeQuery);
            mysqli_stmt_bind_param($employeeStmt, "issssdiss", $userID, $firstname, $lastname, $birthdate, $address, $netsalary, $contactnumber, $deptID, $gender);
            mysqli_stmt_execute($employeeStmt);

            // Insert into points table
            $pointsQuery = "INSERT INTO points (userID, performancePoints, seminarPoints, attendancePoints, deductPoints) 
                            VALUES (?, 0, 0, 0, 0)";
            $pointsStmt = mysqli_prepare($con, $pointsQuery);
            mysqli_stmt_bind_param($pointsStmt, "i", $userID);
            mysqli_stmt_execute($pointsStmt);

            mysqli_commit($con);

            $response = [
                "success" => true,
                "message" => "Employee record inserted successfully",
                "data" => [
                    "userID" => $userID,
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "birthdate" => $birthdate,
                    "address" => $address,
                    "netsalary" => $netsalary,
                    "contactnumber" => $contactnumber,
                    "deptID" => $deptID,
                    "gender" => $gender
                ]
            ];
        } catch (Exception $e) {
            mysqli_rollback($con);
            $response = ["success" => false, "message" => $e->getMessage()];
        }

        mysqli_close($con);
    } else {
        $response = ["success" => false, "message" => "Missing parameters: " . implode(', ', $missingParams)];
    }
} else {
    $response = ["success" => false, "message" => "Invalid request method: POST required"];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
