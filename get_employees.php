<?php
header('Content-Type: application/json');

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection function
function dbconnection() {
    $host = 'localhost'; // Change this to your database host
    $user = 'root'; // Change this to your database username
    $password = ''; // Change this to your database password
    $database = 'hr'; // Change this to your database name

    $con = new mysqli($host, $user, $password, $database);

    // Check connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    return $con;
}

$con = dbconnection();

// Handle GET request to fetch employees
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT userID, firstName, lastName, birthdate, address, netSalary, photo, contactNumber, deptID FROM employee"; 
    $result = $con->query($sql);

    $employee = [];

    if ($result->num_rows > 0) {
        // Fetch all employees
        while ($row = $result->fetch_assoc()) {
            $employee[] = $row;
        }
    }

    // Return the result as JSON
    echo json_encode($employee);
}

// Handle POST request to add a new employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the POST request (assuming JSON format)
    $input = json_decode(file_get_contents("php://input"), true);

    // Extract employee data from the request
    $firstName = $input['firstName'] ?? '';
    $lastName = $input['lastName'] ?? '';
    $birthdate = $input['birthdate'] ?? '';
    $address = $input['address'] ?? '';
    $netSalary = $input['netSalary'] ?? '';
    $photo = $input['photo'] ?? '';
    $contactNumber = $input['contactNumber'] ?? '';
    $deptID = $input['deptID'] ?? '';

    // Check if the required data is provided
    if (!empty($firstName) && !empty($lastName)) {
        // Prepare SQL query to insert new employee
        $insert_sql = "INSERT INTO employee (firstName, lastName, birthdate, address, netSalary, photo, contactNumber, deptID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $con->prepare($insert_sql)) {
            $stmt->bind_param("ssssssis", $firstName, $lastName, $birthdate, $address, $netSalary, $photo, $contactNumber, $deptID);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Employee added successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add employee"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to prepare insert statement"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Missing required employee data"]);
    }
}

// Close the database connection
$con->close();
?>
