<?php
// send_otp.php
date_default_timezone_set('Africa/Nairobi');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendOTP($toEmail, $otp, $purpose = 'reset') {
    $brandName = 'Jovic Park Rentals';
    $subject = 'Your OTP Code - ' . ucfirst($purpose);

    $mail = new PHPMailer(true);
    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        // TODO: Replace these with your Gmail + App Password (not the login pw)
        $mail->Username   = 'reny.technologies@gmail.com';
        $mail->Password   = 'nmsd olha duot vxfa';
        // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // From / To
        $mail->setFrom('reny.technologies@gmail.com', $brandName);
        $mail->addAddress($toEmail);

        // Optional embedded logo
        $logoCid = null;
        $logoPath = __DIR__ . '/assets/uploads/favicon.jpg';
        if (file_exists($logoPath)) {
            $logoCid = 'logo' . md5(uniqid('', true));
            $mail->addEmbeddedImage($logoPath, $logoCid);
        }

        // Email body (HTML)
        $otpCard = "
            <div style='background:#f4f7fb;padding:24px;font-family:Arial,Helvetica,sans-serif;'>
                <div style='max-width:560px;margin:auto;background:#ffffff;padding:24px;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,0.08);'>
                    <div style='text-align:center;margin-bottom:12px;'>
                        ".($logoCid ? "<img src='cid:$logoCid' alt='Logo' style='height:56px;margin-bottom:8px;border-radius:8px;'/>" : "")."
                        <h2 style='margin:0;color:#111111;font-size:22px;font-weight:700;letter-spacing:0.3px;'>$brandName</h2>
                    </div>
                    <h3 style='color:#007baf;margin:16px 0 8px 0;font-size:20px;'>🔑 OTP Verification</h3>
                    <p style='color:#333333;font-size:15px;line-height:1.6;margin:0 0 12px 0;'>
                        Use the One-Time Password below to complete your <strong>".htmlspecialchars($purpose)."</strong> verification. 
                        This code is valid for <strong>10 minutes</strong>.
                    </p>
                    <div style='background:#007baf;color:#ffffff;text-align:center;font-size:28px;font-weight:800;padding:14px 12px;border-radius:10px;letter-spacing:6px;margin:18px 0;'>
                        ".htmlspecialchars($otp)."
                    </div>
                    <p style='color:#666;font-size:13px;margin:0 0 16px 0;'>
                        If you didn’t request this, you can safely ignore this email.
                    </p>
                    <div style='text-align:center;color:#999;font-size:12px;margin-top:12px;'>
                        &copy; ".date('Y')." $brandName. All rights reserved.
                    </div>
                </div>
            </div>
        ";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $otpCard;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo ?: $e->getMessage();
    }
}
