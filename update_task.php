<?php
/**
 * Update Task Status Endpoint
 * POST: Update task status
 */

ob_start();
ini_set('display_errors', 0);
error_reporting(0);
ob_clean();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$conn = @new mysqli("localhost", "root", "", "teamcommander");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}
$conn->set_charset("utf8mb4");

// Get JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if ($data === null) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// Get task ID and status
$task_id = isset($data['id']) ? intval($data['id']) : 0;
$new_status = isset($data['status']) ? $data['status'] : '';

if ($task_id <= 0) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Invalid task ID: " . $task_id]);
    exit;
}

if (!in_array($new_status, ['completed', 'not_completed'])) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Invalid status: " . $new_status]);
    exit;
}

// Update the task status using a simple query
$query = "UPDATE events SET status = '" . $conn->real_escape_string($new_status) . "' WHERE id = " . $task_id;
$result = $conn->query($query);

if ($result) {
    if ($conn->affected_rows > 0) {
        ob_end_clean();
        echo json_encode([
            "status" => "success",
            "message" => "Task status updated"
        ]);
    } else {
        ob_end_clean();
        echo json_encode([
            "status" => "success",
            "message" => "No changes made"
        ]);
    }
} else {
    ob_end_clean();
    echo json_encode([
        "status" => "error",
        "message" => "Query failed: " . $conn->error
    ]);
}

$conn->close();
?>
