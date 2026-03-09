<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_functions.php';

if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

$db = (new Database())->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validateEmail($email) || empty($password)) {
        $error = 'Invalid login credentials';
    } else {

        $stmt = $db->prepare("
            SELECT user_id, password_hash, user_type, is_active
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (
            !$user ||
            $user['user_type'] !== 'admin' ||
            !$user['is_active'] ||
            !password_verify($password, $user['password_hash'])
        ) {
            $error = 'Access denied';
        } else {

            // Login success
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_type'] = 'admin';

            // Update last login
            $db->prepare("
                UPDATE users SET last_login = NOW() WHERE user_id = ?
            ")->execute([$user['user_id']]);

            header('Location: admin_dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-bg">

<div class="auth-container">

    <div class="auth-card admin-card">

        <div class="auth-header">
            <img src="../assets/images/logo.png" alt="kejaMtaani logo">
            <h1>keja<span>Mtaani</span></h1>
            <p>Administrator Access</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">
                Secure Login
            </button>

        </form>

        <div class="auth-footer">
            <small>Restricted area • Authorized personnel only</small>
        </div>

    </div>

</div>

</body>
</html>
