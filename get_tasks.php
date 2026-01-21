<?php
/**
 * Get Tasks Endpoint
 * GET: Retrieve all tasks
 */

ob_start();
ini_set('display_errors', 0);
error_reporting(0);
ob_clean();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = new mysqli("localhost", "root", "", "teamcommander");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}
$conn->set_charset("utf8mb4");

// Get all tasks - now with proper id column
$query = "SELECT * FROM events ORDER BY created_at DESC";
$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Query failed"]);
    $conn->close();
    exit;
}

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $task = [
        "id" => isset($row['id']) ? (int)$row['id'] : 0,
        "task_name" => $row['task_name'] ?? "",
        "task_description" => $row['task_description'] ?? "",
        "task_date" => $row['task_date'] ?? "",
        "due_date" => $row['due_date'] ?? "",
        "created_at" => $row['created_at'] ?? "",
        "status" => $row['status'] ?? "not_completed",
        "assigned_to" => isset($row['assigned_to']) ? (int)$row['assigned_to'] : null,
        "assigned_to_name" => "Unassigned"
    ];
    
    // Get assigned user name
    if (!empty($row['assigned_to']) && $row['assigned_to'] > 0) {
        $user_result = $conn->query("SELECT full_name FROM users WHERE id = " . (int)$row['assigned_to']);
        if ($user_result && $user_row = $user_result->fetch_assoc()) {
            $task["assigned_to_name"] = $user_row['full_name'];
        }
    }
    
    $tasks[] = $task;
}

$conn->close();

ob_end_clean();
echo json_encode([
    "status" => "success",
    "count" => count($tasks),
    "tasks" => $tasks
]);
?>
