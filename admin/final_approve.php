<?php
session_start();
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: verify_landlord.php?error=invalid_request");
    exit;
}

$userId = (int) $_GET['id'];
$db = (new Database())->getConnection();


$stmt = $db->prepare("
    SELECT user_id, user_type, otp_status, is_verified 
    FROM users 
    WHERE user_id = ? 
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: verify_landlord.php?error=user_not_found");
    exit;
}


if ($user['user_type'] !== 'landlord') {
    header("Location: verify_landlord.php?error=invalid_user_type");
    exit;
}

if ($user['is_verified'] == 1) {
    header("Location: verify_landlord.php?info=already_verified");
    exit;
}


if ($user['otp_status'] !== 'passed') {
    header("Location: verify_landlord.php?id=$userId&error=otp_not_verified");
    exit;
}


$approve = $db->prepare("
    UPDATE users 
    SET is_verified = 1, 
        otp_status = 'none',
        otp_hash = NULL,
        otp_expiry = NULL,
        otp_attempts = 0,
        otp_sent_count = 0
    WHERE user_id = ?
");

if ($approve->execute([$userId])) {
    
    header("Location: verify_landlord.php?id=$userId&success=landlord_approved");
    exit;
} else {
    echo "An error occurred during final approval.";
}