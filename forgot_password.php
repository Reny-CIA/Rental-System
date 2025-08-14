<?php
session_start();
include('./db_connect.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in users table
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Save to password_resets table
        $insert = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $email, $otp, $expires);
        $insert->execute();

        // Send OTP email
        $subject = "Your Password Reset OTP";
        $message = "Your OTP is: $otp \nIt will expire in 10 minutes.";
        $headers = "From: noreply@yourdomain.com";
        mail($email, $subject, $message, $headers);

        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $msg = "Email not found in our system.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body style="background: url('assets/uploads/house-bg.jpg') no-repeat center center/cover; display: flex; align-items: center; justify-content: center; height: 100vh;">
<div style="background: rgba(255,255,255,0.8); padding: 30px; border-radius: 10px; width: 350px;">
    <h3 style="text-align:center;color:#007baf;">Forgot Password</h3>
    <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Enter your email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top:10px;">Send OTP</button>
    </form>
</div>
</body>
</html>
