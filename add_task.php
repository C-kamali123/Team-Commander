<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validation
if (
    !isset($data['task_name']) ||
    !isset($data['task_description']) ||
    !isset($data['task_date']) ||
    !isset($data['due_date'])
) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

$task_name = trim($data['task_name']);
$task_description = trim($data['task_description']);
$task_date = $data['task_date'];
$due_date = $data['due_date'];

// Insert query
$stmt = $conn->prepare(
    "INSERT INTO events (task_name, task_description, task_date, due_date)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssss",
    $task_name,
    $task_description,
    $task_date,
    $due_date
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Event added successfully",
        "event_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add event"
    ]);
}

$stmt->close();
$conn->close();
?>
