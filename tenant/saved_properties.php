<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$saved_kejas = [];

try {
    // Fetch only properties this user has saved
    $stmt = $db->prepare("
        SELECT p.*, 
        (SELECT file_path FROM property_media WHERE property_id = p.property_id AND media_type = 'image' LIMIT 1) as thumb
        FROM properties p
        JOIN saved_properties s ON p.property_id = s.property_id
        WHERE s.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $saved_kejas = $stmt->fetchAll();
} catch (PDOException $e) {
    // This will help you debug if the column name is still wrong
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Kejas | Saved Properties</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <link rel="stylesheet" href="assets/tenant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<?php include 'components/tenant_nav.php'; ?>

<div class="property-view-wrapper">
    <div class="saved-header">
        <h1>My Kejas 🔖</h1>
        <p>You have <strong><?= count($saved_kejas) ?></strong> properties saved.</p>
    </div>

    <div class="saved-grid">
        <?php if (empty($saved_kejas)): ?>
            <p>You haven't saved any properties yet. <a href="tenant_dashboard.php">Browse houses</a></p>
        <?php else: ?>
            <?php foreach ($saved_kejas as $keja): ?>
            <div class="keja-card">
                <a href="view_property.php?id=<?= $keja['property_id'] ?>" class="keja-link">
                    <img src="../<?= $keja['thumb'] ?? 'assets/images/placeholder.jpg' ?>" class="keja-img">
                    <div class="keja-body">
                        <h3 class="keja-title"><?= htmlspecialchars($keja['title']) ?></h3>
                        <div class="keja-price">KES <?= number_format($keja['rent_amount']) ?></div>
                        <p class="keja-loc">📍 <?= htmlspecialchars($keja['locality']) ?></p>
                    </div>
                </a>
                <div class="remove-save" title="Remove" onclick="if(confirm('Remove this property?')) location.href='api/toggle_save.php?remove=<?= $keja['property_id'] ?>'">
                    <i class="fa-solid fa-trash"></i>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>