<?php
// verify_otp.php
session_start();
date_default_timezone_set('Africa/Nairobi');

header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, otp, expires_at, used 
    FROM otps 
    WHERE email=? AND used=0 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['status' => 'error', 'msg' => 'No OTP found or already used']);
    exit;
}

if ($row['otp'] !== $otp) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid OTP']);
    exit;
}

if (strtotime($row['expires_at']) <= time()) {
    echo json_encode(['status' => 'error', 'msg' => 'OTP expired']);
    exit;
}

// Mark as used
$upd = $conn->prepare("UPDATE otps SET used=1 WHERE id=?");
$upd->bind_param("i", $row['id']);
$upd->execute();
$upd->close();

// At this point you can mark the user/session as verified for the specific purpose
echo json_encode(['status' => 'success', 'msg' => 'OTP verified']);
