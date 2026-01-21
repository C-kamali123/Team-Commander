<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept");

require 'vendor/autoload.php';
require 'db.php';   // <-- DB connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'])) {
    echo json_encode(['status' => 400, 'message' => 'Email required']);
    exit;
}

$email = $data['email'];


// 🔍 Fetch user details using email
$query = $conn->prepare(
    "SELECT id, full_name FROM users WHERE email = ?"
);
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 404, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
$userId   = $user['id'];
$userName = $user['full_name'];


// 🔢 Generate 6-digit OTP
$otp = rand(100000, 999999);


// 💾 Save OTP in database
$update = $conn->prepare(
    "UPDATE users SET OTP = ? WHERE email = ?"
);
$update->bind_param("ss", $otp, $email);
$update->execute();


// ✉️ Send OTP Email
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

    $mail->AddEmbeddedImage('image/zootopia.jpg', 'app_logo');

    $mail->isHTML(true);
    $mail->Subject = 'Forgot Password - OTP Verification';

    $mail->Body = "
        <div style='font-family:Arial;padding:20px'>
            <div style='text-align:center'>
                <img src='cid:app_logo' width='120'><br><br>
                <h2>Forgot Password OTP</h2>
            </div>

            <p>Dear <b>$userName</b>,</p>

            <p>Your OTP to reset your password is:</p>

            <h1 style='background:#e9f5ff;padding:15px;text-align:center'>
                $otp
            </h1>

            <p>This OTP is valid for a short time.  
               Do not share it with anyone.</p>

            <p style='text-align:center;color:#777'>
                Team Commander<br>Chennai
            </p>
        </div>
    ";

    $mail->AltBody = "Your OTP is: $otp";

    $mail->send();

    echo json_encode([
        'status' => 200,
        'message' => 'OTP sent successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Mail error: ' . $mail->ErrorInfo
    ]);
}
?>
