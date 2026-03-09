<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['user_type'] !== 'admin'
) {
    header('Location: admin_login.php');
    exit;
}