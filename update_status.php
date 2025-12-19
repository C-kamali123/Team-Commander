<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['task_id']) ||
    !isset($data['status'])
) {
    echo json_encode([
        "status" => "error",
        "message" => "Task ID and status are required"
    ]);
    exit;
}

$task_id = intval($data['task_id']);
$status = $data['status'];

// Validate status value
if (!in_array($status, ['completed', 'not_completed'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid status value"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE events SET status = ? WHERE id = ?"
);
$stmt->bind_param("si", $status, $task_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Task status updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Task not found or status unchanged"
    ]);
}

$stmt->close();
$conn->close();
?>
