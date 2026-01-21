<?php
/**
 * Debug: Show table structure
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost", "root", "", "teamcommander");
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$result = $conn->query("DESCRIBE events");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
}

echo json_encode(["columns" => $columns]);
$conn->close();
?>
