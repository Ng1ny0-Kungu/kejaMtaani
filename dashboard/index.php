<?php
session_start();
require_once __DIR__ . '/../includes/auth_functions.php';


if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/select_role.php');
    exit;
}

$role = $_SESSION['user_type'] ?? '';

if ($role === 'tenant') {
    header('Location: tenant_dashboard.php');
} elseif ($role === 'landlord') {
    header('Location: landlord_dashboard.php');
} elseif ($role === 'admin') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: ../auth/login.php');
}
exit;