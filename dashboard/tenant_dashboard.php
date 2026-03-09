<?php
session_start();


if (empty($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    header('Location: ../auth/login.php?role=tenant');
    exit;
}

include_once __DIR__ . '/../includes/nav.php';
?>

<h1>Welcome to your Tenant Dashboard, <?php echo $_SESSION['name']; ?>!</h1>