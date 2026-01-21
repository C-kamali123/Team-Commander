<?php
/**
 * Create Task Endpoint
 * POST: Create a new task with member assignment
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate JSON body
if ($data === null) {
    echo json_encode(["status" => "error", "message" => "JSON body not received"]);
    exit;
}

// Validate required fields
$required = ['task_name', 'task_description', 'task_date', 'due_date'];
foreach ($required as $field) {
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        echo json_encode(["status" => "error", "message" => ucfirst(str_replace('_', ' ', $field)) . " is required"]);
        exit;
    }
}

$task_name = trim($data['task_name']);
$task_description = trim($data['task_description']);
$task_date = trim($data['task_date']);
$due_date = trim($data['due_date']);
$status = isset($data['status']) ? trim($data['status']) : 'not_completed';
$assigned_to = isset($data['assigned_to']) ? (int)$data['assigned_to'] : null;

// Validate status
if (!in_array($status, ['completed', 'not_completed'])) {
    $status = 'not_completed';
}

// Validate dates
if (!strtotime($task_date) || !strtotime($due_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format. Use YYYY-MM-DD"]);
    exit;
}

// Check if assigned_to column exists, if not add it
$check_column = $conn->query("SHOW COLUMNS FROM events LIKE 'assigned_to'");
if ($check_column->num_rows === 0) {
    $conn->query("ALTER TABLE events ADD COLUMN assigned_to INT NULL");
}

// Insert task with assigned member
if ($assigned_to !== null && $assigned_to > 0) {
    $stmt = $conn->prepare("INSERT INTO events (task_name, task_description, task_date, due_date, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $task_name, $task_description, $task_date, $due_date, $status, $assigned_to);
} else {
    $stmt = $conn->prepare("INSERT INTO events (task_name, task_description, task_date, due_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $task_name, $task_description, $task_date, $due_date, $status);
}

if ($stmt->execute()) {
    $task_id = $stmt->insert_id;
    
    // Get assigned member name if assigned
    $assigned_name = null;
    if ($assigned_to !== null && $assigned_to > 0) {
        $member_stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $member_stmt->bind_param("i", $assigned_to);
        $member_stmt->execute();
        $member_result = $member_stmt->get_result();
        if ($member_row = $member_result->fetch_assoc()) {
            $assigned_name = $member_row['full_name'];
        }
        $member_stmt->close();
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Task created successfully",
        "task" => [
            "id" => $task_id,
            "task_name" => $task_name,
            "task_description" => $task_description,
            "task_date" => $task_date,
            "due_date" => $due_date,
            "status" => $status,
            "assigned_to" => $assigned_to,
            "assigned_name" => $assigned_name
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create task: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
