<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Debug mode ON for development
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Check both message and department
    if (!empty($input['message']) && !empty($input['department'])) {
        $message = htmlspecialchars($input['message']);
        $department = htmlspecialchars($input['department']);

        include("dbconnection.php");
        $con = dbconnection();

        // Use department in query
        $query = "INSERT INTO notices (message, department, created_at) VALUES (?, ?, NOW())";

        if ($stmt = mysqli_prepare($con, $query)) {
            mysqli_stmt_bind_param($stmt, "ss", $message, $department);

            if (mysqli_stmt_execute($stmt)) {
                $response = [
                    "success" => true,
                    "message" => "✅ Notice saved successfully"
                ];
            } else {
                $response = [
                    "success" => false,
                    "message" => "❌ Execute failed: " . mysqli_error($con)
                ];
            }

            mysqli_stmt_close($stmt);
        } else {
            $response = [
                "success" => false,
                "message" => "❌ Prepare failed: " . mysqli_error($con)
            ];
        }

        mysqli_close($con);
    } else {
        $response = [
            "success" => false,
            "message" => "❌ 'message' and 'department' are required"
        ];
    }
} else {
    $response = [
        "success" => false,
        "message" => "❌ Invalid request method: POST required"
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
