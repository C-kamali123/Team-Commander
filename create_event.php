<?php
/**
 * Create Event Endpoint
 * POST: Create a new scheduled event with optional participants
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
$required = ['event_name', 'event_place', 'event_date', 'event_time'];
foreach ($required as $field) {
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        echo json_encode(["status" => "error", "message" => ucfirst(str_replace('_', ' ', $field)) . " is required"]);
        exit;
    }
}

$event_name = trim($data['event_name']);
$event_place = trim($data['event_place']);
$event_date = trim($data['event_date']);
$event_time = trim($data['event_time']);
$description = isset($data['description']) ? trim($data['description']) : '';
$status = isset($data['status']) ? trim($data['status']) : 'not_completed';
$participants = isset($data['participants']) ? $data['participants'] : [];

// Validate status
if (!in_array($status, ['completed', 'not_completed'])) {
    $status = 'not_completed';
}

// Validate date
if (!strtotime($event_date)) {
    echo json_encode(["status" => "error", "message" => "Invalid date format. Use YYYY-MM-DD"]);
    exit;
}

// Check if description column exists, if not add it
$check_column = $conn->query("SHOW COLUMNS FROM events_schedule LIKE 'description'");
if ($check_column->num_rows === 0) {
    $conn->query("ALTER TABLE events_schedule ADD COLUMN description TEXT NULL AFTER event_name");
}

// Insert event with description
$stmt = $conn->prepare("INSERT INTO events_schedule (event_name, description, event_place, event_date, event_time, status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $event_name, $description, $event_place, $event_date, $event_time, $status);

if ($stmt->execute()) {
    $event_id = $stmt->insert_id;
    
    // Handle participants if provided
    $participant_count = 0;
    if (!empty($participants) && is_array($participants)) {
        // Check if event_participants table exists, if not create it
        $conn->query("CREATE TABLE IF NOT EXISTS event_participants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_participant (event_id, user_id)
        )");
        
        // Insert participants
        $participant_stmt = $conn->prepare("INSERT IGNORE INTO event_participants (event_id, user_id) VALUES (?, ?)");
        foreach ($participants as $user_id) {
            $participant_stmt->bind_param("ii", $event_id, $user_id);
            if ($participant_stmt->execute()) {
                $participant_count++;
            }
        }
        $participant_stmt->close();
    }
    
    echo json_encode([
        "status" => "success",
        "message" => "Event created successfully",
        "event" => [
            "id" => $event_id,
            "event_name" => $event_name,
            "description" => $description,
            "event_place" => $event_place,
            "event_date" => $event_date,
            "event_time" => $event_time,
            "status" => $status,
            "participant_count" => $participant_count
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create event: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
