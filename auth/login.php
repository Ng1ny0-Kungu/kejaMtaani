<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_functions.php';

redirectIfLoggedInUserOnly();

$role = $_GET['role'] ?? null;
$errors = [];

if (!in_array($role, ['tenant', 'landlord'])) {
    header('Location: select_role.php?mode=login');
    exit;
}

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifier = sanitizeInput($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (is_numeric($identifier)) {
        $formattedPhone = validatePhone($identifier);
        if ($formattedPhone) {
            $identifier = $formattedPhone;
        }
    }

    if (empty($identifier) || empty($password)) {
        $errors['general'] = 'All fields are required';
    }

    if (empty($errors)) {

        $stmt = $db->prepare("
            SELECT *
            FROM users
            WHERE (email = :id OR phone_number = :id)
              AND user_type = :role
              AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':id'   => $identifier,
            ':role' => $role
        ]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors['general'] = 'Invalid login credentials';
        } else {

            
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['name']      = $user['first_name'];

            
            if ($user['user_type'] === 'landlord' && !$user['is_verified']) {
                header('Location: verify_account.php');
                exit;
            }

            
            if ($user['user_type'] === 'tenant') {
                header('Location: ../tenant/tenant_dashboard.php'); 
            } else {
                header('Location: ../dashboard/landlord_dashboard.php');
            }
            exit;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($role) ?> Login | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-bg">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="../assets/images/logo.png" alt="Logo">
            <h1>keja<span>Mtaani</span></h1>
            <p><?= ucfirst($role) ?> Login</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="info-box error" style="color: red; margin-bottom: 10px;">
                <?= $errors['general'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Email or Phone</label>
                <input type="text" name="identifier" placeholder="Email or 07xxxxxxxx" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
            <div class="auth-footer">
                <p>Don’t have an account? <a href="select_role.php?mode=signup">Sign up</a></p>
            </div>
        </form>
    </div>
</div>
</body>
</html>