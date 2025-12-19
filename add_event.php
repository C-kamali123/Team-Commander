<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['event_name']) ||
    !isset($data['event_place']) ||
    !isset($data['event_date']) ||
    !isset($data['event_time'])
) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

$event_name  = trim($data['event_name']);
$event_place = trim($data['event_place']);
$event_date  = $data['event_date'];
$event_time  = $data['event_time'];

$stmt = $conn->prepare(
    "INSERT INTO events_schedule (event_name, event_place, event_date, event_time)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $event_name, $event_place, $event_date, $event_time);

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
