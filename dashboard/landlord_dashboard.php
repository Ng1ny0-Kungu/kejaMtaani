<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: ../auth/login.php?role=landlord');
    exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT is_verified FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_verified']) {
    header('Location: ../auth/verify_account.php');
    exit;
}

/* Fetch Landlord Properties */
$listingsStmt = $db->prepare("
    SELECT property_id, title, status, moderation_status
    FROM properties
    WHERE landlord_id = ?
    ORDER BY created_at DESC
");
$listingsStmt->execute([$_SESSION['user_id']]);
$listings = $listingsStmt->fetchAll();

$statsStmt = $db->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status = 'available' AND moderation_status = 'approved') as live_listings,
        SUM(status = 'draft') as drafts,
        SUM(moderation_status = 'pending_review') as pending_review,
        SUM(moderation_status = 'rejected') as rejected
    FROM properties
    WHERE landlord_id = ?
");
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Landlord Dashboard | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../dashboard/partials/landlord_nav.php'; ?>

<div class="dashboard-container">

    <h1>Landlord Dashboard</h1>
    <h3 class="welcome-row">
        <span class="welcome-text">
            Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
        </span>

        <?php if ($user['is_verified']): ?>
            <img src="../assets/images/verified.png" 
               alt="Verified"
               class="verified-badge">
        <?php endif; ?>
    </h3>

    <div class="stats-grid">
        <div class="card">
            <h3>Total Listings</h3>
            <p><?php echo $stats['total'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Available</h3>
            <p><?php echo $stats['live_listings'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Drafts</h3>
            <p><?php echo $stats['drafts'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Pending Review</h3>
            <p><?php echo $stats['pending_review'] ?? 0; ?></p>
        </div>
    </div>

    <div class="actions">
        <a href="add_property.php" class="btn-primary">
            + Add New Listing
        </a>
    </div>

</div>

<div class="dashboard-container">
    <hr>
    <h2>Your Listings</h2>

<?php if (count($listings) > 0): ?>

    <table class="listing-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Moderation</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listings as $property): ?>
                <tr>
                    <td><?php echo htmlspecialchars($property['title']); ?></td>

                    <td><?php echo ucfirst($property['status']); ?></td>

                    <td>
                        <?php echo ucfirst(str_replace('_', ' ', $property['moderation_status'])); ?>
                    </td>

                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="edit_property.php?id=<?php echo $property['property_id']; ?>" class="btn-edit">
                                Edit
                            </a>

                            <a href="../controllers/property/delete_property.php?id=<?php echo $property['property_id']; ?>" 
                                class="btn-logout" 
                                style="background: #e74c3c; padding: 6px 12px; font-size: 13px;"
                                onclick="return confirm('Are you sure you want to permanently delete this listing? This action cannot be undone.')">
                                 Delete
                            </a>
                        </div>

                        <?php if ($property['moderation_status'] === 'approved'): ?>
                            <small style="display:block; color:#27ae60;">(Live & Approved)</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?> 
    <p>You have no listings yet.</p>
<?php endif; ?>
</div>
</body>
</html>