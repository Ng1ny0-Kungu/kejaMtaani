<?php

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends the 6-digit OTP for initial registration/verification
 */
function sendVerificationEmail($toEmail, $toName, $otp)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP(); 
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iam.kejamtaani@gmail.com';
        $mail->Password   = 'tioflkjwciyoyqdk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('iam.kejamtaani@gmail.com', 'Keja Mtaani Admin');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Landlord Verification OTP - Keja Mtaani';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h3>Hello $toName,</h3>
                <p>Your verification code is:</p>
                <h2 style='color:#2f8fa5; letter-spacing: 5px;'>$otp</h2>
                <p>This code expires in 15 minutes.</p>
                <br>
                <p>Regards,<br><strong>Keja Mtaani Admin</strong></p>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sends a notification when the admin officially approves the account
 */
function sendApprovalEmail($toEmail, $toName)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iam.kejamtaani@gmail.com';
        $mail->Password   = 'tioflkjwciyoyqdk'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('iam.kejamtaani@gmail.com', 'Keja Mtaani Admin');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Account Activated - Keja Mtaani';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h3>Congratulations $toName!</h3>
                <p>Your landlord account has been <strong>fully verified</strong> by our admin team.</p>
                <p>The verification badge has been granted to your profile. You can now access all landlord features and list your properties.</p>
                <br>
                <a href='http://localhost/keja_Mtaani/auth/login.php?role=landlord' 
                   style='background:#2f8fa5; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block;'>
                   Go to Dashboard
                </a>
                <br><br>
                <p>Regards,<br><strong>Keja Mtaani Admin</strong></p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}