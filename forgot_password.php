<?php
/**
 * Forgot Password Endpoint
 * POST: Handle password reset request - Send OTP via email
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

require 'vendor/autoload.php';
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

$email = trim($data['email']);
$role = isset($data['role']) ? trim($data['role']) : null;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit;
}

// Check if email exists in database (no role filter - works for any user)
$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$userName = $user['full_name'];
$stmt->close();

// Generate 6-digit OTP
$otp = rand(100000, 999999);

// Save OTP in database
$update = $conn->prepare("UPDATE users SET OTP = ? WHERE email = ?");
$update->bind_param("ss", $otp, $email);
$update->execute();
$update->close();

// Send OTP Email using PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'chinnikamali@gmail.com';
    $mail->Password = 'ufcq pnkv fbqh esnp'; // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('chinnikamali@gmail.com', 'Team Commander');
    $mail->addAddress($email, $userName);

    // Check if logo image exists before embedding
    if (file_exists('image/zootopia.jpg')) {
        $mail->AddEmbeddedImage('image/zootopia.jpg', 'app_logo');
        $logoHtml = "<img src='cid:app_logo' width='120'><br><br>";
    } else {
        $logoHtml = "";
    }

    $mail->isHTML(true);
    $mail->Subject = 'Forgot Password - OTP Verification';

    $mail->Body = "
        <div style='font-family:Arial;padding:20px'>
            <div style='text-align:center'>
                $logoHtml
                <h2>Forgot Password OTP</h2>
            </div>

            <p>Dear <b>$userName</b>,</p>

            <p>Your OTP to reset your password is:</p>

            <h1 style='background:#e9f5ff;padding:15px;text-align:center;color:#2563EB;letter-spacing:5px'>
                $otp
            </h1>

            <p>This OTP is valid for a short time. Do not share it with anyone.</p>

            <p style='text-align:center;color:#777'>
                Team Commander<br>Chennai
            </p>
        </div>
    ";

    $mail->AltBody = "Your OTP is: $otp";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "OTP sent successfully to your email"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send email: " . $mail->ErrorInfo
    ]);
}

$conn->close();
?>
