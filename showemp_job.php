<?php
include("dbconnection.php");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Create a new database connection
$con = dbconnection();

// Check if the database connection is successful
if ($con->connect_error) {
    $response = array(
        "success" => false,
        "message" => "Database connection failed: " . $con->connect_error
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Prepare the SQL statement
$query = "SELECT 
    employee.userID AS employeeUserID,
    users_table.userID AS usersTableUserID,
    employee.firstName, 
    employee.lastName, 
    employee.birthdate, 
    employee.address, 
    employee.netSalary, 
    employee.photo, 
    employee.contactNumber, 
    dept.deptName, 
    GROUP_CONCAT(DISTINCT jobtitle.title SEPARATOR ', ') AS titles,
    users_table.email
FROM 
    employee
JOIN 
    dept 
ON 
    employee.deptID = dept.deptID
JOIN 
    jobtitle 
ON 
    employee.deptID = jobtitle.departmentID
LEFT JOIN
    users_table
ON
    employee.userID = users_table.userID
GROUP BY 
    employee.userID, users_table.userID, dept.deptName, employee.contactNumber, users_table.email";


// Execute the statement
$result = $con->query($query);

// Check if there are any records
if ($result) {
    if ($result->num_rows > 0) {
        $records = array();
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($records);
    } else {
        $response = array(
            "success" => false,
            "message" => "No records found"
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    $response = array(
        "success" => false,
        "message" => "Query execution failed: " . $con->error
    );
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close the database connection
$con->close();
?>