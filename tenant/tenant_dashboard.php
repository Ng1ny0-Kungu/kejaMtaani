<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tenant') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

// --- NEW SAVE LOGIC ---
if (isset($_POST['toggle_save'])) {
    $property_id = $_POST['property_id'];
    
    // Check if already saved
    $check = $conn->prepare("SELECT 1 FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $check->execute([$user_id, $property_id]);
    
    if ($check->fetch()) {
        // Unsave
        $stmt = $conn->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
    } else {
        // Save
        $stmt = $conn->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $property_id]);
    }
    // Reload to update state
    header("Location: tenant_dashboard.php");
    exit();
}

$stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch properties and their saved status for the current user
$propStmt = $conn->prepare("
    SELECT p.*, 
    (SELECT COUNT(*) FROM saved_properties sp WHERE sp.property_id = p.property_id AND sp.user_id = ?) as is_saved
    FROM properties p 
    WHERE moderation_status = 'approved' 
    AND status = 'available' 
    ORDER BY is_featured DESC, created_at DESC
");
$propStmt->execute([$user_id]);
$properties = $propStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/tenant.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>

<body>

<?php include 'components/tenant_nav.php'; ?>

<div class="dashboard-container">

    <h1 class="welcome">
        Welcome, <?php echo htmlspecialchars($user['first_name']); ?>
    </h1>

    <div class="search-section">
        <input type="text" id="searchLocation" placeholder="Search by town or mtaa">

        <select id="propertyType">
            <option value="">Property Type</option>
            <option value="two_bedroom">2 Bedroom</option>
            <option value="one_bedroom">1 Bedroom</option>
            <option value="bedsitter">Bedsitter</option>
            <option value="single">Single</option>
        </select>

        <input type="number" id="minPrice" placeholder="Min Rent">
        <input type="number" id="maxPrice" placeholder="Max Rent">

        <button id="searchBtn">Search</button>
    </div>

    <h2 class="section-title">Featured Properties</h2>

    <div class="property-grid">
        <?php if ($properties): ?>
            <?php foreach ($properties as $property): ?>
                
                <?php 
                    // We pass the $property array which now contains 'is_saved' 
                    include 'components/property_card.php'; 
                ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-properties" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                <p> No verified properties available in your mtaa yet. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="assets/tenant.js"></script>

</body>
</html>