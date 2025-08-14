<?php
// generate_otp.php
session_start();
date_default_timezone_set('Africa/Nairobi');

header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/send_otp.php';

$email   = trim($_POST['email'] ?? '');
$purpose = trim($_POST['purpose'] ?? 'reset'); // 'reset' | 'login' | 'signup'

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid email address']);
    exit;
}

// Optional: enforce known user for reset/login
// $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
// $check->bind_param("s", $email);
// $check->execute();
// $exists = $check->get_result()->num_rows > 0;
// $check->close();
// if (!$exists && $purpose !== 'signup') {
//     echo json_encode(['status' => 'error', 'msg' => 'Email not found']);
//     exit;
// }

$otp         = strval(random_int(100000, 999999));
$expires_at  = date('Y-m-d H:i:s', time() + 10 * 60); // +10 minutes

$stmt = $conn->prepare("INSERT INTO otps (email, otp, purpose, expires_at) VALUES (?,?,?,?)");
$stmt->bind_param("ssss", $email, $otp, $purpose, $expires_at);
$saved = $stmt->execute();
$stmt->close();

if (!$saved) {
    echo json_encode(['status' => 'error', 'msg' => 'Failed to store OTP']);
    exit;
}

$sent = sendOTP($email, $otp, $purpose);
if ($sent === true) {
    echo json_encode(['status' => 'success', 'msg' => 'OTP sent to your email']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Email error: '.$sent]);
}
