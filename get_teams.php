<?php
/**
 * Get Teams Endpoint
 * GET: Get all teams for dropdown selection
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

// Get all teams
$sql = "SELECT id, team_name, description FROM teams ORDER BY team_name ASC";
$result = $conn->query($sql);

$teams = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teams[] = [
            "id" => (int)$row['id'],
            "team_name" => $row['team_name'],
            "description" => $row['description']
        ];
    }
}

echo json_encode([
    "status" => "success",
    "teams" => $teams
]);

$conn->close();
?>
