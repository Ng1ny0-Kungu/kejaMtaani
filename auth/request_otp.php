<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];


$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otp_hash = password_hash($otp, PASSWORD_DEFAULT);
$expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));


$stmtUser = $db->prepare("SELECT email, first_name FROM users WHERE user_id = ?");
$stmtUser->execute([$user_id]);
$userData = $stmtUser->fetch();


$stmt = $db->prepare("
    UPDATE users 
    SET otp_hash = ?, otp_expiry = ?, otp_status = 'sent', otp_attempts = 0
    WHERE user_id = ? AND is_verified = 0
");

if ($stmt->execute([$otp_hash, $expiry, $user_id])) {
    
    sendVerificationEmail($userData['email'], $userData['first_name'], $otp);

    
    header("Location: verify_account.php?resent=1");
} else {
    header("Location: verify_account.php?error=fail");
}
exit;