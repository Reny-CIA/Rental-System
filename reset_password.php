<?php
session_start();
include('./db_connect.php');

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$msg = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = trim($_POST['otp']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $msg = "Passwords do not match.";
    } else {
        // Check OTP
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND otp = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashedPassword, $email);
            $update->execute();

            // Clear session
            unset($_SESSION['reset_email']);
            $msg = "Password successfully updated. <a href='login.php'>Login</a>";
        } else {
            $msg = "Invalid or expired OTP.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body style="background: url('assets/uploads/house-bg.jpg') no-repeat center center/cover; display: flex; align-items: center; justify-content: center; height: 100vh;">
<div style="background: rgba(255,255,255,0.8); padding: 30px; border-radius: 10px; width: 350px;">
    <h3 style="text-align:center;color:#007baf;">Reset Password</h3>
    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Enter OTP</label>
            <input type="text" name="otp" class="form-control" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:10px;">Reset Password</button>
    </form>
</div>
</body>
</html>
