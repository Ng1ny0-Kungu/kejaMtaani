<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: login.php?role=landlord');
    exit;
}

$db = (new Database())->getConnection();

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";


if (isset($_GET['resent'])) {
    $message = "A new verification code has been sent to your email.";
}
if (isset($_GET['error']) && $_GET['error'] === 'wrong_otp') {
    $error = "The OTP you entered is incorrect. Please try again.";
}
if (isset($_GET['error']) && $_GET['error'] === 'expired') {
    $error = "Your OTP has expired. Please request a new one below.";
}

$stmt = $db->prepare("
    SELECT is_verified, otp_status 
    FROM users 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php?role=landlord');
    exit;
}

if ($user['is_verified']) {
    header('Location: ../dashboard/landlord_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Account | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-bg">

<div class="auth-container">
    <div class="auth-card">

        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Account Verification</h2>
        </div>

        <hr>

        <?php if (!empty($message)): ?>
            <p style="color: green; background: #e8f5e9; padding: 10px; border-radius: 4px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: #c62828; background: #ffebee; padding: 10px; border-radius: 4px;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <?php if ($user['otp_status'] === 'sent' || $user['otp_status'] === 'none'): ?>
            <p>
                Please check your email (<strong><?= htmlspecialchars($_SESSION['email'] ?? 'your registered email') ?></strong>) for a 6-digit verification code sent from:<br>
                <strong>iam.kejamtaani@gmail.com</strong>
            </p>

            <form method="POST" action="process_landlord_otp.php" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Enter OTP Code</label>
                    <input type="text" name="otp_code" placeholder="000000" maxlength="6" required 
                           style="letter-spacing: 5px; font-size: 1.2rem; text-align: center;">
                </div>

                <button type="submit" class="btn-primary">
                    Submit OTP
                </button>
            </form>

            <p style="margin-top: 25px; font-size: 0.9em;">
                Didn't receive the code? 
                <a href="request_otp.php" style="color: #2196F3; text-decoration: none; font-weight: bold;">Resend OTP</a>
            </p>

        <?php elseif ($user['otp_status'] === 'passed'): ?>

            <div style="text-align: center; padding: 20px 0;">
                <div style="color: green; font-size: 1.1em; margin-bottom: 15px;">
                    <strong>✔ OTP Verified Successfully</strong>
                </div>
                <p>
                    Your account is now <strong>awaiting admin approval</strong>. <br>
                    We are reviewing your uploaded documents. You will receive an email once your dashboard is activated.
                </p>
            </div>

        <?php elseif ($user['otp_status'] === 'blocked'): ?>
            <p style="color: #c62828;">
                <strong>Account Suspended:</strong> Too many incorrect OTP attempts. Please contact support at iam.kejamtaani@gmail.com.
            </p>
        <?php endif; ?>

        <hr style="margin: 20px 0;">
        <a href="logout.php" style="color: #666; text-decoration: none;">Logout</a>

    </div>
</div>

</body>
</html>