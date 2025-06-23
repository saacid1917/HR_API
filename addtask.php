<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include("dbconnection.php");

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get JSON input
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Extract values from input
        $projectID = $data['projectID'] ?? null;
        $userID = $data['userID'] ?? null;
        $module = $data['module'] ?? '';
        $task = $data['task'] ?? '';
        $deadline = $data['deadline'] ?? null;
        $date = $data['date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'pending';
        $address = $data['address'] ?? '';
        $completeTime = $data['completeTime'] ?? null;
        $startDate = date('Y-m-d H:i:s'); // Auto timestamp
        $distance = 0; // Default to 0
        
        // Validate required fields
        if (!$projectID || !$userID || !$module || !$task) {
            throw new Exception("Missing required fields: projectID, userID, module, or task");
        }
        
        $con = dbconnection();

        // Check if projectID exists
        $stmt = $con->prepare("SELECT projectID FROM project WHERE projectID = ?");
        $stmt->bind_param("i", $projectID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Invalid projectID: No matching project found");
        }
        $stmt->close();

        // Prepare SQL statement
        $stmt = $con->prepare("INSERT INTO task (projectID, userID, module, task, deadline, date, status, address, completeTime, startDate, distance) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssssd", $projectID, $userID, $module, $task, $deadline, $date, $status, $address, $completeTime, $startDate, $distance);
        
        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Task added successfully", "taskID" => $stmt->insert_id];
        } else {
            throw new Exception("Error: " . $stmt->error);
        }

        $stmt->close();
        $con->close();
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

// Output JSON response
echo json_encode($response);
?>
