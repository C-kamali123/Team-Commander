<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include "db.php";

$result = $conn->query(
    "SELECT id, event_name, event_place, event_date, event_time, created_at
     FROM events_schedule
     ORDER BY created_at DESC"
);

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $events
]);

$conn->close();
?>
