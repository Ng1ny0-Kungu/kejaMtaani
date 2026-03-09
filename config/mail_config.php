<?php

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            <h3>Hello $toName,</h3>
            <p>Your verification code is:</p>
            <h2 style='color:#2e7d32;'>$otp</h2>
            <p>This code expires in 15 hours.</p>
            <br>
            <p>Regards,<br>Keja Mtaani Admin</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
