<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: ../auth/login.php?role=landlord");
    exit;
}

if (!isset($_GET['id'])) {
    die("Property not found.");
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("
    SELECT * FROM properties 
    WHERE property_id = ? AND landlord_id = ?
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Unauthorized access.");
}

$mediaStmt = $db->prepare("
    SELECT * FROM property_media WHERE property_id = ?
");
$mediaStmt->execute([$property['property_id']]);
$mediaFiles = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body>

<form method="POST"
      action="../controllers/property/update_property.php"
      enctype="multipart/form-data">

<div class="edit-container">

    <!-- LEFT SIDE: MEDIA SECTION -->
    <div class="media-section">
        <h2>Listing Media</h2>

        <div class="media-grid">
            <?php foreach ($mediaFiles as $file): ?>
                <div class="media-card">
                    <?php if ($file['media_type'] === 'image'): ?>
                        <img src="../<?php echo htmlspecialchars($file['file_path']); ?>" 
                             class="zoomable-image">
                    <?php else: ?>
                        <video src="../<?php echo htmlspecialchars($file['file_path']); ?>" controls></video>
                    <?php endif; ?>

                    <a href="../controllers/property/delete_media.php?id=<?php echo $file['media_id']; ?>&property=<?php echo $property['property_id']; ?>"
                       class="delete-media"
                       onclick="return confirm('Delete this media?')">
                        Delete
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="upload-box">
            <h3>Add More Media</h3>

            <label>Images</label>
            <input type="file" id="imageInput" name="images[]" accept="image/*" multiple>

            <label>Videos</label>
            <input type="file" id="videoInput" name="videos[]" accept="video/*" multiple>

   
            <div id="previewGrid" class="media-preview-grid"></div>
        </div>
    </div>

    <!-- RIGHT SIDE: PROPERTY DETAILS -->
    <div class="form-section">
        <h2>Edit Details</h2>

        <input type="hidden" name="property_id"
               value="<?php echo $property['property_id']; ?>">

        <div class="form-group">
            <label>Property Title</label>
            <input type="text" name="title"
                   value="<?php echo htmlspecialchars($property['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>Property Type</label>
            <select name="property_type">
                <option value="two_bedroom" <?php if($property['property_type']=='two_bedroom') echo 'selected'; ?>>Two Bedroom</option>
                <option value="one_bedroom" <?php if($property['property_type']=='one_bedroom') echo 'selected'; ?>>One Bedroom</option>
                <option value="Bedsitter" <?php if($property['property_type']=='Bedsitter') echo 'selected'; ?>>Bedsitter</option>
                <option value="Single Room" <?php if($property['property_type']=='Single Room') echo 'selected'; ?>>Single Room</option>
            </select>
        </div>

        <div class="form-group">
            <label>Rent Amount (KES)</label>
            <input type="number" name="rent_amount"
                   value="<?php echo $property['rent_amount']; ?>" required>
        </div>

        <div class="form-group">
            <label>Deposit Amount (KES)</label>
            <input type="number" name="deposit_amount"
                   value="<?php echo $property['deposit_amount']; ?>">
        </div>

        <div class="form-group">
            <label>Electricity Payment</label>
            <select name="electricity_payment">
                <option value="individual_token" <?php if($property['electricity_payment']=='individual_token') echo 'selected'; ?>>Individual Token</option>
                <option value="Included in Rent" <?php if($property['electricity_payment']=='Included in Rent') echo 'selected'; ?>>Included in Rent</option>
            </select>
        </div>

        <div class="form-group">
            <label>Water Payment</label>
            <select name="water_payment">
                <option value="individual_payment" <?php if($property['water_payment']=='individual_payment') echo 'selected'; ?>>Individual Payment</option>
                <option value="included_in_rent" <?php if($property['water_payment']=='included_in_rent') echo 'selected'; ?>>Included in Rent</option>
            </select>
        </div>

        <div class="features-group">
            <label><input type="checkbox" name="has_wifi" <?php if($property['has_wifi']) echo 'checked'; ?>> Wi-Fi</label>
            <label><input type="checkbox" name="has_balcony" <?php if($property['has_balcony']) echo 'checked'; ?>> Balcony</label>
            <label><input type="checkbox" name="has_rooftop_access" <?php if($property['has_rooftop_access']) echo 'checked'; ?>> Rooftop Access</label>
            <label><input type="checkbox" name="has_parking" <?php if($property['has_parking']) echo 'checked'; ?>> Parking</label>
        </div>

        <button type="submit" class="btn-primary">
            Update Property
        </button>

    </div>
</div>

</form> 

<script src="../assets/js/media_preview.js"></script>

</body>
</html>