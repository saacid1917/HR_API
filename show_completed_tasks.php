<?php
include("dbconnection.php");

// CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Connect DB
$con = dbconnection();
if (!$con) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Fetch all completed tasks
$query = "
    SELECT 
        task.taskID,
        task.task,
        task.module,
        employee.firstName AS completedBy,
        task.completeTime,
        task.submissionFile,
        task.fileType
    FROM task
    JOIN employee ON task.userID = employee.userID
    WHERE task.status = 'completed'
    ORDER BY task.completeTime DESC
";

$result = mysqli_query($con, $query);

$completedTasks = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $completedTasks[] = $row;
    }
    echo json_encode($completedTasks, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["success" => false, "message" => "Query failed: " . mysqli_error($con)]);
}

mysqli_close($con);
?>
