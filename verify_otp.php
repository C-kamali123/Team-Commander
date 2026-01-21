<?php
/**
 * Verify OTP Endpoint
 * POST: Verify OTP for password reset
 */
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate JSON body
if ($data === null) {
    echo json_encode(["status" => "error", "message" => "JSON body not received"]);
    exit;
}

// Validate required fields
if (!isset($data['email']) || trim($data['email']) === '') {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

if (!isset($data['otp']) || trim($data['otp']) === '') {
    echo json_encode(["status" => "error", "message" => "OTP is required"]);
    exit;
}

$email = trim($data['email']);
$otp = trim($data['otp']);

// Fetch user with matching email and OTP
$stmt = $conn->prepare("SELECT id, full_name, email, role, OTP FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if OTP matches
if ($user['OTP'] === null || $user['OTP'] === '') {
    echo json_encode(["status" => "error", "message" => "No OTP requested. Please request a new OTP."]);
    $conn->close();
    exit;
}

if ($user['OTP'] !== $otp) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP. Please try again."]);
    $conn->close();
    exit;
}

// OTP verified - Clear OTP from database
$update = $conn->prepare("UPDATE users SET OTP = NULL WHERE email = ?");
$update->bind_param("s", $email);
$update->execute();
$update->close();

// Return success with user details
echo json_encode([
    "status" => "success",
    "message" => "OTP verified successfully",
    "user" => [
        "id" => $user['id'],
        "full_name" => $user['full_name'],
        "email" => $user['email'],
        "role" => $user['role']
    ]
]);

$conn->close();
?>
