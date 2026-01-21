<?php
/**
 * Member Login Endpoint
 * POST: Authenticate team member
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
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Email and Password are required"]);
    exit;
}

$email = trim($data['email']);
$password = trim($data['password']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit;
}

// Query user by email and role = team_member
$stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'team_member'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid email or not a team member"]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

// Verify password (supports both bcrypt hashed and plain text passwords)
$passwordValid = false;

// Check if password is bcrypt hashed (starts with $2y$)
if (strpos($user['password'], '$2y$') === 0) {
    $passwordValid = password_verify($password, $user['password']);
} else {
    // Plain text comparison (for legacy passwords)
    $passwordValid = ($password === $user['password']);
}

if ($passwordValid) {
    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => (int)$user['id'],
            "full_name" => $user['full_name'],
            "email" => $user['email'],
            "role" => $user['role']
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid password"]);
}

$stmt->close();
$conn->close();
?>
