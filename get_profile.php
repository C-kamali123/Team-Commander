<?php
/**
 * Get Profile Endpoint
 * GET/POST: Retrieve user profile by ID
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "db.php";

// Get user ID from query string or POST body
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
} else {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;
}

if (!$user_id || $user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit;
}

// Query user profile
$stmt = $conn->prepare("SELECT id, full_name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => (int)$user['id'],
        "full_name" => $user['full_name'],
        "email" => $user['email'],
        "role" => $user['role'],
        "created_at" => $user['created_at']
    ]
]);

$stmt->close();
$conn->close();
?>
