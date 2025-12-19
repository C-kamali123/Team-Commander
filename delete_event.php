<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['event_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Event ID is required"
    ]);
    exit;
}

$event_id = intval($data['event_id']);

$stmt = $conn->prepare("DELETE FROM events_schedule WHERE id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Event deleted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Event not found"
    ]);
}

$stmt->close();
$conn->close();
?>
