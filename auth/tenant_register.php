<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_functions.php';

redirectIfLoggedInUserOnly();


$db = (new Database())->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and Validate Inputs using your auth_functions
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name  = sanitizeInput($_POST['last_name'] ?? '');
    $email      = sanitizeInput($_POST['email'] ?? '');
    $phone      = validatePhone($_POST['phone'] ?? ''); // Standardizes to 254...
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    /* ---------- VALIDATION ---------- */
    if (empty($first_name) || empty($last_name)) $errors[] = 'Full name is required.';
    
    if (!validateEmail($email)) {
        $errors[] = 'Invalid email address.';
    } elseif (emailExists($db, $email)) {
        $errors[] = 'Email already registered.';
    }

    if (!$phone) {
        $errors[] = 'Invalid phone format (use 07... or 254...).';
    } elseif (phoneExists($db, $phone)) {
        $errors[] = 'Phone number already registered.';
    }

    if (!validatePassword($password)) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    /* ---------- PROCESS ---------- */
    if (empty($errors)) {
        try {
            $password_hash = hashPassword($password);

            // Tenants are auto-verified (is_verified = 1)
            $stmt = $db->prepare("
                INSERT INTO users (
                    email, 
                    phone_number, 
                    password_hash, 
                    first_name, 
                    last_name, 
                    user_type, 
                    is_verified, 
                    verification_token, 
                    token_expiry, 
                    last_login, 
                    is_active
                ) VALUES (
                    :email, 
                    :phone, 
                    :password, 
                    :first, 
                    :last, 
                    'tenant', 
                    1, 
                    NULL, 
                    NULL, 
                    NOW(), 
                    1
                )
            ");

            $stmt->execute([
                ':email'    => $email,
                ':phone'    => $phone,
                ':password' => $password_hash,
                ':first'    => $first_name,
                ':last'     => $last_name
            ]);

            header('Location: login.php?registered=1');
            exit;

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Tenant Account | KejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-bg">

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-header">
            <img src="../assets/images/logo.png" alt="KejaMtaani Logo">
            <h1>keja<span>Mtaani</span></h1>
            <p>Find and manage your rental home easily</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: #fee; color: #b00; padding: 12px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #fcc; font-size: 14px;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="example@gmail.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required placeholder="07xxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>

        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>

    </div>
</div>

</body>
</html>