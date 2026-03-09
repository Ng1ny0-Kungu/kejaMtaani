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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_otp'])) {

    $stmt = $db->prepare("
        UPDATE users 
        SET otp_status = 'requested'
        WHERE user_id = ?
          AND is_verified = 0
    ");
    $stmt->execute([$user_id]);

    $message = "OTP request submitted. Please wait for admin to send the code to your email.";
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

        <p>
            Please press the button below  to request an OTP and then check your email for a verification code sent from:<br>
            <strong>iam.kejamtaani@gmail.com</strong>
            <?php if ($user['otp_status'] === 'none'): ?>
                <form method="POST" style="margin:0;">
                    <button type="submit" name="request_otp" class="btn-primary">
                        Request OTP
                    </button>
                </form>
            <?php endif; ?>
        </p>

        <?php if (!empty($message)): ?>
            <p style="color: green; margin-top:10px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if ($user['otp_status'] === 'sent'): ?>

            <p style="color: orange;">
                OTP has been sent to your email. Please enter it below.
            </p>

            <form method="POST" action="process_landlord_otp.php">
                <div class="form-group">
                    <label>Enter OTP</label>
                    <input type="text" name="otp_code" required>
                </div>

                <button type="submit" class="btn-primary">
                    Submit OTP
                </button>
            </form>

        <?php elseif ($user['otp_status'] === 'passed'): ?>

            <p style="color: green;">
                OTP submitted successfully.<br>
                Awaiting admin approval.
            </p>

        <?php endif; ?>

        <br>
        <a href="logout.php">Logout</a>

    </div>
</div>

</body>
</html>
