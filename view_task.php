<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include "db.php";

$result = $conn->query(
    "SELECT id, task_name, task_description, task_date, due_date, created_at
     FROM events
     ORDER BY created_at DESC"
);

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

if (count($events) > 0) {
    echo json_encode([
        "status" => "success",
        "data" => $events
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "No events found",
        "data" => []
    ]);
}

$conn->close();
?>
