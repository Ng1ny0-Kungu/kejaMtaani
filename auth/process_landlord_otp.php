<?php
session_start();
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit;
}

$db = (new Database())->getConnection();
$userId = $_SESSION['user_id'];


$enteredOtp = trim($_POST['otp_code'] ?? ''); 


$stmt = $db->prepare("SELECT otp_hash, otp_expiry, otp_status, otp_attempts FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();


if (!$user || $user['otp_status'] !== 'sent') {
    header("Location: login.php?error=invalid_session"); 
    exit;
}


if (strtotime($user['otp_expiry']) < time()) {
    header("Location: verify_account.php?error=expired");
    exit;
}


if (password_verify($enteredOtp, $user['otp_hash'])) {
    
    $success = $db->prepare("UPDATE users SET otp_status = 'passed' WHERE user_id = ?");
    $success->execute([$userId]);
    
    
    session_destroy();
    header("Location: login.php?success=otp_verified_awaiting_admin");
    exit;
} else {
    
    $newAttempts = $user['otp_attempts'] + 1;
    if ($newAttempts >= 5) {
        $db->prepare("UPDATE users SET otp_status = 'blocked' WHERE user_id = ?")->execute([$userId]);
        session_destroy();
        header("Location: login.php?error=too_many_attempts");
    } else {
        $db->prepare("UPDATE users SET otp_attempts = ? WHERE user_id = ?")->execute([$newAttempts, $userId]);
        header("Location: verify_account.php?error=wrong_otp");
    }
    exit;
}