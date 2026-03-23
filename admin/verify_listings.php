<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php?role=admin");
    exit;
}

$db = (new Database())->getConnection();

if (!isset($_GET['landlord_id'])) {
    die("Landlord ID missing.");
}

$landlord_id = $_GET['landlord_id'];

// Fixed Query: Concatenating names to avoid "Column not found" error
$landlordStmt = $db->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name, email FROM users WHERE user_id = ?");
$landlordStmt->execute([$landlord_id]);
$landlord = $landlordStmt->fetch();

$listingsStmt = $db->prepare("SELECT * FROM properties WHERE landlord_id = ? ORDER BY created_at DESC");
$listingsStmt->execute([$landlord_id]);
$listings = $listingsStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Listings | Admin</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <style>
        /* Specific Admin Tweaks */
        .admin-header { background: #1e2a38; color: white; padding: 20px 5%; display: flex; justify-content: space-between; align-items: center; }
        .verification-container { padding: 40px 5%; }
        .property-divider { border-bottom: 3px solid #00bcd4; margin: 40px 0; }
        .status-badge-admin { padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        .pending { background: #f39c12; color: white; }
        .approved { background: #27ae60; color: white; }
        .rejected { background: #e74c3c; color: white; }
    </style>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<div class="admin-header">
    <div class="brand">Reviewing: <span><?php echo htmlspecialchars($landlord['full_name']); ?></span></div>
    <a href="admin_dashboard.php" class="nav-link" style="color:white; text-decoration:none;">← Back to Dashboard</a>
</div>

<div class="verification-container">

<?php foreach ($listings as $property): ?>

    <div class="edit-container" style="background: white; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 50px;">
        
        <div class="media-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Listing Media</h2>
                <span class="status-badge-admin <?php echo $property['moderation_status']; ?>">
                    <?php echo $property['moderation_status']; ?>
                </span>
            </div>

            <div class="media-grid">
                <?php
                $mStmt = $db->prepare("SELECT * FROM property_media WHERE property_id = ? AND is_active = 1");
                $mStmt->execute([$property['property_id']]);
                $mediaFiles = $mStmt->fetchAll();

                foreach ($mediaFiles as $file): ?>
                    <div class="media-card">
                        <?php if ($file['media_type'] === 'image'): ?>
                            <img src="../<?php echo htmlspecialchars($file['file_path']); ?>" onclick="openModal(this.src)" style="cursor:pointer">
                        <?php else: ?>
                            <video src="../<?php echo htmlspecialchars($file['file_path']); ?>" controls></video>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="upload-box">
                <h3>Location Map</h3>
                <iframe width="100%" height="220" style="border-radius:8px; border:1px solid #ddd;"
                    src="https://maps.google.com/maps?q=<?php echo $property['latitude']; ?>,<?php echo $property['longitude']; ?>&z=15&output=embed">
                </iframe>
            </div>
        </div>

        <div class="form-section">
            <h2>Property Details</h2>
            
            <div class="profile-grid">
                <div><h4>Title</h4><p><?php echo htmlspecialchars($property['title']); ?></p></div>
                <div><h4>Type</h4><p><?php echo ucfirst(str_replace('_',' ',$property['property_type'])); ?></p></div>
                <div><h4>Rent</h4><p>KES <?php echo number_format($property['rent_amount']); ?></p></div>
                <div><h4>Deposit</h4><p><?php echo $property['deposit_amount'] ? "KES ".number_format($property['deposit_amount']) : "No Deposit"; ?></p></div>
            </div>

            <div class="profile-grid">
                <div><h4>Electricity</h4><p><?php echo ucfirst(str_replace('_',' ',$property['electricity_payment'])); ?></p></div>
                <div><h4>Water</h4><p><?php echo ucfirst(str_replace('_',' ',$property['water_payment'])); ?></p></div>
            </div>

            <div class="profile-bio">
                <h4>Address</h4>
                <p><?php echo htmlspecialchars($property['locality'].", ".$property['ward'].", ".$property['county']); ?></p>
            </div>

            <div class="features-group" style="margin-top:20px;">
                <label><input type="checkbox" disabled <?php echo $property['has_wifi'] ? 'checked' : ''; ?>> Wi-Fi</label>
                <label><input type="checkbox" disabled <?php echo $property['has_balcony'] ? 'checked' : ''; ?>> Balcony</label>
                <label><input type="checkbox" disabled <?php echo $property['has_parking'] ? 'checked' : ''; ?>> Parking</label>
                <label><input type="checkbox" disabled <?php echo $property['has_rooftop_access'] ? 'checked' : ''; ?>> Rooftop</label>
            </div>

            <div class="profile-actions" style="border-top: 1px solid #eee; padding-top: 20px; display: flex; gap: 10px;">
                <form action="approve_listing.php" method="POST" style="flex:1;">
                    <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                    <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Approve</button>
                </form>

                <form action="reject_listing.php" method="POST" style="flex:1;">
                    <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                    <button type="submit" class="btn-logout" style="width:100%; border:none; cursor:pointer;">Reject</button>
                </form>
            </div>
        </div>
    </div>

<?php endforeach; ?>

</div>

<div id="mediaModal" class="media-modal">
    <span class="close-modal" onclick="closeModal()">×</span>
    <div class="modal-content"><img id="modalImage" style="max-width:100%"></div>
</div>

<script>
function openModal(src){ document.getElementById("mediaModal").style.display="flex"; document.getElementById("modalImage").src=src; }
function closeModal(){ document.getElementById("mediaModal").style.display="none"; }
</script>

</body>
</html>