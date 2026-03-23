<?php
session_start();

require_once __DIR__ . '/../includes/auth_functions.php';

/* If someone is already logged in */
redirectIfLoggedInUserOnly();

$mode = $_GET['mode'] ?? 'signup';
$isLogin = ($mode === 'login');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Role | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/role.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<header>
    <a href="../welcome.php" class="logo">
        <img src="../assets/images/logo.png" alt="Logo">
        <span>kejaMtaani</span>
    </a>
</header>

<main class="role-container">
    <h1><?= $isLogin ? "Login as" : "Sign up as" ?></h1>
    <p>Select your account type</p>

    <div class="roles">

        <a href="<?= $isLogin ? 'login.php?role=tenant' : 'tenant_register.php' ?>" class="role-card">
            <img src="../assets/images/Tenant-icon.png" class="role-icon">
            <h2>Tenant</h2>
            <p>Find rental homes & manage preferences</p>
        </a>

        <a href="<?= $isLogin ? 'login.php?role=landlord' : 'landlord_register.php' ?>" class="role-card">
            <img src="../assets/images/Landlord-icon.png" class="role-icon">
            <h2>Landlord</h2>
            <p>List properties & manage tenants</p>
        </a>

    </div>

    <div class="switch">
        <?php if ($isLogin): ?>
            Don’t have an account?
            <a href="select_role.php?mode=signup">Sign Up</a>
        <?php else: ?>
            Already have an account?
            <a href="select_role.php?mode=login">Login</a>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
