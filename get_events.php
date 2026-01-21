<?php
/**
 * Get Events Endpoint
 * GET: Retrieve scheduled events with optional filters
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";

// Get optional filters
$status = isset($_GET['status']) ? trim($_GET['status']) : null;
$date = isset($_GET['date']) ? trim($_GET['date']) : null;
$upcoming = isset($_GET['upcoming']) ? true : false;
$today_only = isset($_GET['today']) ? true : false;

// Check if description column exists
$check_desc = $conn->query("SHOW COLUMNS FROM events_schedule LIKE 'description'");
$has_description = ($check_desc->num_rows > 0);

// Build query
$select_fields = "e.id, e.event_name, e.event_place, e.event_date, e.event_time, e.created_at, e.status";
if ($has_description) {
    $select_fields .= ", e.description";
}

// Check if event_participants table exists
$check_participants = $conn->query("SHOW TABLES LIKE 'event_participants'");
$has_participants = ($check_participants->num_rows > 0);

if ($has_participants) {
    $query = "SELECT $select_fields, 
              (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) as participant_count
              FROM events_schedule e";
} else {
    $query = "SELECT $select_fields FROM events_schedule e";
}

$params = [];
$types = "";
$conditions = [];

if ($status && in_array($status, ['completed', 'not_completed'])) {
    $conditions[] = "e.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($date && strtotime($date)) {
    $conditions[] = "e.event_date = ?";
    $params[] = $date;
    $types .= "s";
}

if ($upcoming) {
    $conditions[] = "e.event_date >= CURDATE()";
}

if ($today_only) {
    $conditions[] = "e.event_date = CURDATE()";
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY e.event_date ASC, e.event_time ASC";

// Prepare and execute
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Query error: " . $conn->error
    ]);
    exit;
}

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $event = [
        "id" => (int)$row['id'],
        "event_name" => $row['event_name'],
        "event_place" => $row['event_place'],
        "event_date" => $row['event_date'],
        "event_time" => $row['event_time'],
        "created_at" => $row['created_at'],
        "status" => $row['status']
    ];
    
    if ($has_description) {
        $event["description"] = isset($row['description']) ? $row['description'] : "";
    }
    
    if ($has_participants) {
        $event["participant_count"] = isset($row['participant_count']) ? (int)$row['participant_count'] : 0;
    }
    
    $events[] = $event;
}

echo json_encode([
    "status" => "success",
    "count" => count($events),
    "events" => $events
]);

$stmt->close();
$conn->close();
?>
