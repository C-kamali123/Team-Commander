<?php
/**
 * Update Profile Endpoint
 * POST: Update user profile
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
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

// Validate user ID
if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit;
}

$user_id = intval($data['user_id']);

// Check if user exists
$stmt = $conn->prepare("SELECT id, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$current_user = $result->fetch_assoc();
$stmt->close();

// Build update query dynamically
$updates = [];
$params = [];
$types = "";

if (isset($data['full_name']) && trim($data['full_name']) !== '') {
    $updates[] = "full_name = ?";
    $params[] = trim($data['full_name']);
    $types .= "s";
}

if (isset($data['email']) && trim($data['email']) !== '') {
    $new_email = trim($data['email']);
    
    // Validate email format
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        $conn->close();
        exit;
    }
    
    // Check if email is already taken by another user
    if ($new_email !== $current_user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Email already taken"]);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }
    
    $updates[] = "email = ?";
    $params[] = $new_email;
    $types .= "s";
}

if (isset($data['password']) && trim($data['password']) !== '') {
    $new_password = trim($data['password']);
    
    // Validate password length
    if (strlen($new_password) < 6) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters"]);
        $conn->close();
        exit;
    }
    
    $updates[] = "password = ?";
    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    $types .= "s";
}

if (count($updates) === 0) {
    echo json_encode(["status" => "error", "message" => "No valid fields to update"]);
    $conn->close();
    exit;
}

// Add user ID to params
$params[] = $user_id;
$types .= "i";

// Execute update
$query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Fetch updated profile
    $stmt->close();
    $stmt = $conn->prepare("SELECT id, full_name, email, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully",
        "user" => [
            "id" => (int)$user['id'],
            "full_name" => $user['full_name'],
            "email" => $user['email'],
            "role" => $user['role'],
            "created_at" => $user['created_at']
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update profile: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
