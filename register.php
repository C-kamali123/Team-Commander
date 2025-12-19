<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Check fields
if (
    !isset($data['full_name']) ||
    !isset($data['email']) ||
    !isset($data['password']) ||
    !isset($data['role'])
) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

$full_name = trim($data['full_name']);
$email     = trim($data['email']);
$password  = trim($data['password']);   // ❗ Storing plain text
$role      = trim($data['role']);

$allowed_roles = ["team_leader", "team_member"];

if (!in_array($role, $allowed_roles)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid role selected"
    ]);
    exit;
}

// Check if email exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email already registered"
    ]);
    exit;
}
$check->close();

// Insert user with plain password
$stmt = $conn->prepare("
    INSERT INTO users (full_name, email, password, role)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $full_name, $email, $password, $role);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Registration successful",
        "data" => [
            "user_id" => $stmt->insert_id,
            "full_name" => $full_name,
            "email" => $email,
            "role" => $role
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to register user"
    ]);
}

$stmt->close();
$conn->close();
?>
