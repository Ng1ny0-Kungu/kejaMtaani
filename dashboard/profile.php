<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: ../auth/login.php?role=landlord");
    exit;
}

$db = (new Database())->getConnection();

// Fetch User Data
$userStmt = $db->prepare("SELECT user_id, email, phone_number, first_name, last_name, is_verified, created_at FROM users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

// Fetch Profile Data
$profileStmt = $db->prepare("SELECT national_id, bio, rating FROM landlord_profiles WHERE user_id = ?");
$profileStmt->execute([$_SESSION['user_id']]);
$profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Stats
$statsStmt = $db->prepare("SELECT COUNT(*) as total_listings FROM properties WHERE landlord_id = ?");
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <link rel="stylesheet" href="../assets/css/profile_custom.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../dashboard/partials/landlord_nav.php'; ?>

<div class="dashboard-container">
    <div class="profile-header">
        <h1>Landlord Profile</h1>
    </div>

    <div class="card profile-card">
        <div class="profile-main-info">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <?php if ($user['is_verified']): ?>
                <span class="verified-status success">✔ Verified Landlord</span>
            <?php else: ?>
                <span class="verified-status warning">⚠ Verification Pending</span>
            <?php endif; ?>
        </div>

        <hr class="profile-hr">

        <div class="profile-grid">
            <div class="info-item">
                <h4>Email Address</h4>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="info-item">
                <h4>Phone Number</h4>
                <p><?php echo htmlspecialchars($user['phone_number']); ?></p>
            </div>
            <div class="info-item">
                <h4>National ID</h4>
                <p><?php echo htmlspecialchars($profile['national_id'] ?? 'Not Linked'); ?></p>
            </div>
            <div class="info-item">
                <h4>Joined On</h4>
                <p><?php echo date("M j, Y", strtotime($user['created_at'])); ?></p>
            </div>
            <div class="info-item">
                <h4>Active Listings</h4>
                <p><?php echo $stats['total_listings']; ?></p>
            </div>
            <div class="info-item">
                <h4>Trust Score</h4>
                <p><?php echo $profile['rating'] ?? '5.00'; ?> / 5.0</p>
            </div>
        </div>

        <?php if (!empty($profile['bio'])): ?>
            <div class="profile-bio">
                <h4>About Me</h4>
                <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="profile-actions">
            <div class="actions-left">
                <a href="../auth/logout.php" class="btn-logout" onclick="return confirm('Logout of your session?');">Logout</a>
            </div>
            <div class="actions-right">
                <a href="../controllers/auth/delete_account.php" 
                   class="btn-delete"
                   onclick="return confirm('WARNING: This will permanently delete your account and all associated property listings. This action is irreversible. Proceed?')">
                   Delete Account
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>