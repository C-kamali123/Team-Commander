<?php
/**
 * Member Registration Endpoint
 * POST: Register a new team member
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
$required = ['full_name', 'email', 'password'];
foreach ($required as $field) {
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        echo json_encode(["status" => "error", "message" => ucfirst(str_replace('_', ' ', $field)) . " is required"]);
        exit;
    }
}

$full_name = trim($data['full_name']);
$email = trim($data['email']);
$password = trim($data['password']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit;
}

// Check password length
if (strlen($password) < 6) {
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters"]);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Store password as plain text (for development only)
$plain_password = $password;

// Insert new team member
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'team_member')");
$stmt->bind_param("sss", $full_name, $email, $plain_password);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    echo json_encode([
        "status" => "success",
        "message" => "Registration successful",
        "user" => [
            "id" => $user_id,
            "full_name" => $full_name,
            "email" => $email,
            "role" => "team_member"
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
