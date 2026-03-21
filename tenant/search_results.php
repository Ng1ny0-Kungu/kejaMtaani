<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: ../login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Get Search Parameters
$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';
$min = $_GET['min'] ?? '';
$max = $_GET['max'] ?? '';

// Build Dynamic Query
$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM saved_properties sp WHERE sp.property_id = p.property_id AND sp.user_id = ?) as is_saved
          FROM properties p 
          WHERE moderation_status = 'approved' AND status = 'available'";

$params = [$user_id];

if (!empty($location)) {
    $query .= " AND (town LIKE ? OR locality LIKE ? OR county LIKE ?)";
    $locParam = "%$location%";
    array_push($params, $locParam, $locParam, $locParam);
}

if (!empty($type)) {
    $query .= " AND property_type = ?";
    $params[] = $type;
}

if (!empty($min)) {
    $query .= " AND rent_amount >= ?";
    $params[] = $min;
}

if (!empty($max)) {
    $query .= " AND rent_amount <= ?";
    $params[] = $max;
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results | KejaMtaani</title>
    <link rel="stylesheet" href="assets/tenant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<?php include 'components/tenant_nav.php'; ?>

<div class="dashboard-container">
    <div class="search-header">
        <a href="tenant_dashboard.php" style="text-decoration: none; color: #00bcd4;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        <h2 class="section-title">Search Results</h2>
        <p style="color: #666;">Showing results for: <strong><?= htmlspecialchars($location ?: 'Everywhere') ?></strong></p>
    </div>

    <div class="property-grid">
        <?php if ($properties): ?>
            <?php foreach ($properties as $property): ?>
                <?php include 'components/property_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
                <p style="font-size: 40px;">🔍</p>
                <h3>No properties found matching those details.</h3>
                <p>Try widening your price range or searching a different mtaa.</p>
                <a href="tenant_dashboard.php" class="btn-view" style="display:inline-block; margin-top:20px; padding: 10px 20px;">Try Again</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/tenant.js"></script>
</body>
</html>