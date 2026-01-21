<?php
/**
 * Add ID column to events table
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "teamcommander");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Check if id column exists
$check = $conn->query("SHOW COLUMNS FROM events LIKE 'id'");
if ($check->num_rows == 0) {
    // Add the id column as primary key
    $result = $conn->query("ALTER TABLE events ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
    if ($result) {
        echo json_encode(["status" => "success", "message" => "Added id column to events table"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add id column: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "success", "message" => "id column already exists"]);
}

$conn->close();
?>
