<?php

function redirectIfLoggedInUserOnly() {
    if (!empty($_SESSION['user_id'])) {

        if ($_SESSION['user_type'] === 'tenant') {
            header('Location: ../dashboard/tenant_dashboard.php');
            exit;
        }

        if ($_SESSION['user_type'] === 'landlord') {

            require_once __DIR__ . '/../config/database.php';
            $db = (new Database())->getConnection();

            $stmt = $db->prepare("SELECT is_verified FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && $user['is_verified']) {
                header('Location: ../dashboard/landlord_dashboard.php');
            } else {
                header('Location: ../auth/verify_account.php');
            }
            exit;
        }


        
        session_destroy();
        header('Location: ../welcome.php');
        exit;
    }
}

function redirectIfAdminLoggedIn() {
    if (!empty($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
        header('Location: ../dashboard/admin_dashboard.php');
        exit;
    }
}



function sanitizeInput($data) {
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);

    if (strlen($phone) === 10 && str_starts_with($phone, '07')) {
        return '254' . substr($phone, 1);
    }

    if (strlen($phone) === 12 && str_starts_with($phone, '254')) {
        return $phone;
    }

    return false;
}

function validatePassword($password) {
    return strlen($password) >= 8;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function emailExists(PDO $db, $email) {
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return (bool) $stmt->fetch();
}

function phoneExists(PDO $db, $phone) {
    $stmt = $db->prepare("SELECT user_id FROM users WHERE phone_number = ?");
    $stmt->execute([$phone]);
    return (bool) $stmt->fetch();
}
