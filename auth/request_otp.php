<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: login.php?role=landlord');
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    UPDATE users 
    SET otp_status = 'requested'
    WHERE user_id = ?
      AND is_verified = 0
");
$stmt->execute([$_SESSION['user_id']]);

header("Location: verify_account.php?requested=1");
exit;
