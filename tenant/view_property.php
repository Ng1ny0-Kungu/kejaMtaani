<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: tenant_dashboard.php");
    exit();
}

$db_obj = new Database();
$db = $db_obj->getConnection();
$property_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// --- SAVE/UNSAVE LOGIC ---
if (isset($_POST['toggle_save']) && $user_id) {
    $checkSave = $db->prepare("SELECT 1 FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $checkSave->execute([$user_id, $property_id]);
    
    if ($checkSave->fetch()) {
        $stmt = $db->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $property_id]);
    }
    header("Location: view_property.php?id=" . $property_id);
    exit();
}

$is_saved = false;
if ($user_id) {
    $saveCheck = $db->prepare("SELECT 1 FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $saveCheck->execute([$user_id, $property_id]);
    $is_saved = (bool)$saveCheck->fetch();
}

$stmt = $db->prepare("
    SELECT p.*, u.first_name, u.last_name, u.phone_number, lp.full_name as landlord_id_name
    FROM properties p
    JOIN users u ON p.landlord_id = u.user_id
    LEFT JOIN landlord_profiles lp ON u.user_id = lp.user_id
    WHERE p.property_id = ?
");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) { die("Property not found."); }

$mStmt = $db->prepare("SELECT * FROM property_media WHERE property_id = ? AND is_active = 1 ORDER BY is_primary DESC");
$mStmt->execute([$property_id]);
$mediaFiles = $mStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> | KejaMtaani</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <link rel="stylesheet" href="assets/tenant.css">
</head>
<body>

<?php include 'components/tenant_nav.php'; ?>

<div class="property-view-wrapper" style="max-width: 1200px; margin: 30px auto; padding: 0 40px;">

    <div class="property-header-row" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
        <h1 class="apt-name" style="margin: 0; font-size: 28px; font-weight: 700; color: #1e2a38;">
            <?= htmlspecialchars($property['title']) ?>
        </h1>
        
        <div class="header-actions" style="display: flex; align-items: center; gap: 20px;">
            <div class="rating-trigger">
    <a href="javascript:void(0);" onclick="toggleRatingModal()" style="color: #00bcd4; text-decoration: none; font-weight: 600;">
        <i class="fa-regular fa-star"></i> Rate Property
    </a>
</div>

<div id="ratingModal" class="rating-modal" style="display: none;">
    <div class="rating-card">
        <button class="close-btn" onclick="toggleRatingModal()">&times;</button>
        <h3>Rate this Property</h3>
        <p>How was your experience with this Keja?</p>
        
        <form action="process_rating.php" method="POST">
            <input type="hidden" name="property_id" value="<?= $property_id ?>">
            
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" class="fa-solid fa-star"></label>
                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" class="fa-solid fa-star"></label>
                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" class="fa-solid fa-star"></label>
                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" class="fa-solid fa-star"></label>
                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" class="fa-solid fa-star"></label>
            </div>

            <textarea name="review_text" placeholder="Optional: Tell us more about the environment, water, or security..." rows="3"></textarea>
            
            <button type="submit" class="btn-submit-rating">Submit Rating</button>
        </form>
    </div>
</div>
            <form method="POST" style="margin: 0;">
                <button type="submit" name="toggle_save" style="background: none; border: none; color: #00bcd4; padding: 0; cursor: pointer; outline: none;">
                    <i class="<?= $is_saved ? 'fa-solid' : 'fa-regular' ?> fa-bookmark" style="font-size: 30px;"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="edit-container" style="background: white; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); display: flex; overflow: hidden; border: 1px solid #f0f0f0;">
        
        <div class="media-section" style="flex: 1.3; padding: 40px; border-right: 1px solid #f0f0f0; background: #fafafa;">
            <h2 style="font-size: 18px; margin-bottom: 20px; color: #555;">Listing Gallery</h2>
            <div class="media-grid">
                <?php foreach ($mediaFiles as $index => $file): ?>
                    <div class="media-card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; cursor: pointer;" onclick="openModal(<?= $index ?>)">
                        <?php if ($file['media_type'] === 'image'): ?>
                            <img src="../<?= htmlspecialchars($file['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <video src="../<?= htmlspecialchars($file['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;"></video>
                            <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-play" style="color: white; font-size: 30px; opacity: 0.8;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="map-box" style="margin-top: 35px;">
                <h3 style="font-size: 16px; margin-bottom: 15px; color: #555;">Location Map</h3>
                <iframe width="100%" height="280" style="border-radius:12px; border:1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05);"
                    src="https://maps.google.com/maps?q=<?= $property['latitude']; ?>,<?= $property['longitude']; ?>&hl=en;z=14&output=embed">
                </iframe>
            </div>
        </div>

        <div class="form-section" style="flex: 1; padding: 40px; background: #fff;">
            <h2 style="font-size: 18px; margin-bottom: 25px; color: #1e2a38; border-bottom: 2px solid #00bcd4; display: inline-block; padding-bottom: 5px;">Property Details</h2>
            
            <div class="profile-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Title</h4><p style="font-weight: 600;"><?= htmlspecialchars($property['title']); ?></p></div>
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Type</h4><p style="font-weight: 600;"><?= ucfirst(str_replace('_',' ',$property['property_type'])); ?></p></div>
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Rent (Monthly)</h4><p style="font-weight: 600; color: #27ae60;">KES <?= number_format($property['rent_amount']); ?></p></div>
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Deposit</h4><p style="font-weight: 600;"><?= $property['deposit_amount'] ? "KES ".number_format($property['deposit_amount']) : "None"; ?></p></div>
            </div>

            <div class="profile-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; border-top: 1px solid #f5f5f5; padding-top: 20px;">
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Electricity</h4><p><?= ucfirst(str_replace('_',' ',$property['electricity_payment'])); ?></p></div>
                <div><h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase;">Water</h4><p><?= ucfirst(str_replace('_',' ',$property['water_payment'])); ?></p></div>
            </div>

            <div class="profile-bio" style="margin-top: 25px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                <h4 style="color: #00bcd4; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Precise Location</h4>
                <p style="font-size: 14px; color: #444;"><?= htmlspecialchars($property['locality'].", ".$property['ward'].", ".$property['constituency'].", ".$property['county']); ?></p>
            </div>

            <div class="features-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 25px;">
                <label style="font-size: 14px;"><input type="checkbox" disabled <?= $property['has_wifi'] ? 'checked' : ''; ?>> Wi-Fi</label>
                <label style="font-size: 14px;"><input type="checkbox" disabled <?= $property['has_balcony'] ? 'checked' : ''; ?>> Balcony</label>
                <label style="font-size: 14px;"><input type="checkbox" disabled <?= $property['has_parking'] ? 'checked' : ''; ?>> Parking</label>
                <label style="font-size: 14px;"><input type="checkbox" disabled <?= $property['has_rooftop_access'] ? 'checked' : ''; ?>> Rooftop</label>
            </div>

            <div class="landlord-contact-footer" style="border-top: 1px solid #eee; padding-top: 25px; margin-top: 30px;">
                <h3 style="font-size: 16px; color: #1e2a38; margin-bottom: 15px;">Managed By</h3>
                <p style="margin: 0; font-weight: 600;"><?= htmlspecialchars($property['landlord_id_name'] ?? $property['first_name'] . ' ' . $property['last_name']) ?></p>
                <p style="margin-top: 5px;"><a href="tel:<?= $property['phone_number'] ?>" style="color: #00bcd4; font-weight: 700; text-decoration: none; font-size: 18px;"><?= $property['phone_number'] ?></a></p>
                <button onclick="window.location.href='tel:<?= $property['phone_number'] ?>'" class="btn-primary" style="width: 100%; margin-top: 20px; border:none; height: 45px; cursor: pointer; border-radius: 8px; font-weight: bold;">Call Landlord</button>
            </div>
        </div>
    </div>
</div>

<div id="mediaModal" class="media-modal">
    <span class="close-modal" onclick="closeModal()">×</span>
    <button class="modal-prev" onclick="changeMedia(-1)">❮</button>
    <button class="modal-next" onclick="changeMedia(1)">❯</button>
    <div class="modal-content" id="modalContainer"></div>
</div>

<script>
const galleryMedia = <?php 
    $jsMedia = [];
    foreach ($mediaFiles as $file) {
        $jsMedia[] = ['path' => '../' . $file['file_path'], 'type' => $file['media_type']];
    }
    echo json_encode($jsMedia); 
?>;

let currentIndex = 0;

function openModal(index) {
    currentIndex = index;
    document.getElementById("mediaModal").style.display = "flex";
    updateModalContent();
}

function closeModal() {
    document.getElementById("mediaModal").style.display = "none";
    document.getElementById("modalContainer").innerHTML = ""; 
}

function changeMedia(step) {
    currentIndex += step;
    if (currentIndex >= galleryMedia.length) currentIndex = 0;
    if (currentIndex < 0) currentIndex = galleryMedia.length - 1;
    updateModalContent();
}

function updateModalContent() {

    const container = document.getElementById("modalContainer");
    const item = galleryMedia[currentIndex];

    if (item.type === 'image') {

        container.innerHTML =
        `<img src="${item.path}" class="modal-media-item anim-fade">`;

    } else {

        container.innerHTML =
        `
        <video class="modal-media-item modal-video anim-fade"
               controls
               preload="metadata">

            <source src="${item.path}">
            Your browser does not support the video tag.

        </video>
        `;

    }
}

function toggleFullScreen() {

    const video = document.getElementById("modalVideo");

    if (!video) return;

    if (video.requestFullscreen) {
        video.requestFullscreen();
    } 
    else if (video.webkitRequestFullscreen) {
        video.webkitRequestFullscreen();
    } 
    else if (video.msRequestFullscreen) {
        video.msRequestFullscreen();
    }

}

document.addEventListener('keydown', function(e) {
    if (document.getElementById("mediaModal").style.display === "flex") {
        if (e.key === "ArrowLeft") changeMedia(-1);
        if (e.key === "ArrowRight") changeMedia(1);
        if (e.key === "Escape") closeModal();
    }
});

window.onclick = function(event) {
    let modal = document.getElementById("mediaModal");
    if (event.target == modal) closeModal();
}
</script>

<script src="assets/tenant.js"></script>

</body>
</html>