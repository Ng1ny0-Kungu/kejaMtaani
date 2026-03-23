<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: ../login.php");
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

$userStmt = $db->prepare("SELECT user_id, email, phone_number, first_name, last_name, is_verified, created_at FROM users WHERE user_id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

$profile = ['national_id' => 'Not Linked', 'bio' => ''];
try {
    $profileStmt = $db->prepare("SELECT national_id, bio FROM tenant_profiles WHERE user_id = ?");
    $profileStmt->execute([$user_id]);
    $fetchedProfile = $profileStmt->fetch(PDO::FETCH_ASSOC);
    if ($fetchedProfile) { $profile = $fetchedProfile; }
} catch (PDOException $e) { }

$statsStmt = $db->prepare("SELECT COUNT(*) as total_saved FROM saved_properties WHERE user_id = ?");
$statsStmt->execute([$user_id]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | KejaMtaani</title>
    <link rel="stylesheet" href="assets/tenant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<?php include 'components/tenant_nav.php'; ?>

<div class="profile-container">
    <div class="profile-header">
        <h1>Tenant Profile</h1>
    </div>

    <div class="profile-card">
        <div class="profile-main-info">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <?php if ($user['is_verified']): ?>
                <span class="status-badge success"><i class="fa-solid fa-circle-check"></i> Verified Tenant</span>
            <?php else: ?>
                <span class="status-badge warning"><i class="fa-solid fa-clock"></i> Verification Pending</span>
            <?php endif; ?>
        </div>

        <hr class="profile-hr">

        <div class="profile-grid">
            <div class="info-item">
                <label>Email Address</label>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="info-item">
                <label>Phone Number</label>
                <p><?php echo htmlspecialchars($user['phone_number']); ?></p>
            </div>
            <div class="info-item">
                <label>National ID</label>
                <p><?php echo htmlspecialchars($profile['national_id'] ?? 'Not Linked'); ?></p>
            </div>
            <div class="info-item">
                <label>Joined On</label>
                <p><?php echo date("M j, Y", strtotime($user['created_at'])); ?></p>
            </div>
            <div class="info-item">
                <label>Saved Kejas</label>
                <p class="highlight-text"><?php echo $stats['total_saved']; ?></p>
            </div>
            <div class="info-item">
                <label>Member Status</label>
                <p>Standard Tenant</p>
            </div>
        </div>

        <?php if (!empty($profile['bio'])): ?>
            <div class="profile-bio">
                <label>About Me</label>
                <p><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="profile-actions">
            <a href="../auth/logout.php" class="btn-logout" onclick="return confirm('Logout?');">Logout</a>
            <a href="../controllers/auth/delete_account.php" class="btn-delete-link" onclick="return confirm('This is permanent. Proceed?')">Delete Account</a>
        </div>
    </div>
</div>

</body>
</html>