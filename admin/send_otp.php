<?php
session_start();
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid request');
}

$db = (new Database())->getConnection();
$landlord_id = (int) $_GET['id'];


$stmt = $db->prepare("
    SELECT u.email, lp.full_name, u.otp_sent_count 
    FROM users u
    JOIN landlord_profiles lp ON u.user_id = lp.user_id
    WHERE u.user_id = ? AND u.user_type = 'landlord'
    LIMIT 1
");
$stmt->execute([$landlord_id]);
$user = $stmt->fetch();

if (!$user) {
    exit('Landlord not found.');
}


if ($user['otp_sent_count'] >= 5) {
    header("Location: verify_landlord.php?id=$landlord_id&error=max_otp_reached");
    exit;
}


$plain_otp = random_int(100000, 999999);
$hashed_otp = password_hash($plain_otp, PASSWORD_DEFAULT);


$expiry = date("Y-m-d H:i:s", strtotime("+15 hours"));


$update = $db->prepare("
    UPDATE users 
    SET otp_hash = ?, 
        otp_expiry = ?, 
        otp_status = 'sent',
        otp_attempts = 0,
        otp_sent_count = otp_sent_count + 1
    WHERE user_id = ?
");

if ($update->execute([$hashed_otp, $expiry, $landlord_id])) {
    
   
    require_once __DIR__ . '/../config/mail_config.php';

    
    $emailSent = sendVerificationEmail(
        $user['email'],
        $user['full_name'],
        $plain_otp
    );

    if ($emailSent) {
        
        header("Location: verify_landlord.php?id=$landlord_id&otp_sent=1");
        exit;
    } else {
        
        echo "The database was updated, but the email failed to send. Check your SMTP settings.";
    }
} else {
    echo "Database error: Could not update OTP information.";
}