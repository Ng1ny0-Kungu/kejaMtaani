<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: ../auth/login.php?role=landlord');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Property | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/landlord.css">
    <script defer src="../assets/js/property_form.js"></script>
    <script defer src="../assets/js/property_map.js"></script>
</head>
<body>

<?php include __DIR__ . '/partials/landlord_nav.php'; ?>

<div class="form-wrapper">
    <form action="../controllers/property/create_property.php" method="POST" enctype="multipart/form-data">

        <div class="form-step active">
            <h2>Basic Property Details</h2>
            <div class="form-group">
                <label>Premise Name</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Property Type</label>
                <select name="property_type" required>
                    <option value="two_bedroom">Two Bedroom</option>
                    <option value="one_bedroom">One Bedroom</option>
                    <option value="bedsitter">Bedsitter</option>
                    <option value="single">Single</option>
                </select>
            </div>
            <div class="form-group">
                <label>Rent Amount (KES)</label>
                <input type="number" name="rent_amount" required>
            </div>
            <div class="form-group">
                <label>Deposit Required?</label>
                <select id="deposit_required">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="form-group hidden" id="deposit_field">
                <label>Deposit Amount (KES)</label>
                <input type="number" name="deposit_amount">
            </div>
            <button type="button" class="next-btn">Next</button>
        </div>

        <div class="form-step">
            <h2>Utilities & Features</h2>
            <div class="form-group">
                <label>Electricity Payment</label>
                <select name="electricity_payment">
                    <option value="individual_token">Individual Token</option>
                    <option value="shared_meter">Shared Meter</option>
                </select>
            </div>
            <div class="form-group">
                <label>Water Payment</label>
                <select name="water_payment">
                    <option value="individual_payment">Individual Payment</option>
                    <option value="included_in_rent">Included in Rent</option>
                </select>
            </div>
            <div class="checkbox-group">
                <label><input type="checkbox" name="has_wifi"> WiFi</label>
                <label><input type="checkbox" name="has_parking"> Parking</label>
                <label><input type="checkbox" name="has_balcony"> Balcony</label>
                <label><input type="checkbox" name="has_rooftop_access"> Rooftop Access</label>
            </div>
            <button type="button" class="prev-btn">Back</button>
            <button type="button" class="next-btn">Next</button>
        </div>

        <div class="form-step">
        <h2>Location Details</h2>

    <div class="form-group">
        <label>County</label>
        <input type="text" name="county" placeholder="e.g. Nairobi" required>
    </div>

    <div class="form-group">
        <label>Constituency</label>
        <input type="text" name="constituency" placeholder="e.g. Roysambu" required>
    </div>

    <div class="form-group">
        <label>Ward</label>
        <input type="text" name="ward" placeholder="e.g. Roysambu" required>
    </div>

    <div class="form-group">
        <label>Locality (Mtaa)</label>
        <input type="text" name="locality" placeholder="e.g. TRM (Thika Road Mall)" required>
    </div>

    <div id="map-preview-container" style="display: none; margin-bottom: 20px;">
        <label>Captured Location Pin:</label>
        <iframe id="map-iframe" width="100%" height="250" 
                style="border-radius:8px; border:1px solid #ddd;" 
                src="" frameborder="0">
        </iframe>
    </div>

    <div class="form-group">
        <button type="button" id="getLocationBtn" class="btn-primary" style="width: 100%;">
            📍 Capture My Current Location
        </button>
        <p id="locationStatus" style="font-size: 13px; margin-top: 8px; text-align: center; color: #666;"></p>
    </div>

    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    <button type="button" class="prev-btn">Back</button>
    <button type="button" class="next-btn">Next</button>
</div>

        <div class="form-step">
            <h2>Upload Media</h2>
            <div class="form-group">
                <label>Upload Images (Max 10)</label>
                <input type="file" name="images[]" id="imageInput" accept="image/*" multiple>
                <div class="preview-grid" id="imagePreview"></div>
            </div>
            <div class="form-group">
                <label>Upload Videos (Max 3)</label>
                <input type="file" name="videos[]" id="videoInput" accept="video/*" multiple>
                <div class="preview-grid" id="videoPreview"></div>
            </div>
            <button type="button" class="prev-btn">Back</button>
            <button type="submit" class="btn-primary">Submit for Review</button>
        </div>

    </form>
</div>

<div id="mediaModal" class="media-modal">
    <span class="close-modal">&times;</span>
    <div class="modal-content" id="modalContent"></div>
</div>



</body>
</html>