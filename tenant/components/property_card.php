<?php
// Extracted variables for cleaner use
$property_id = $property['property_id'];
$title = $property['title'];
$rent = $property['rent_amount'];
$town = $property['town'];
$type = $property['property_type'];
$wifi = $property['has_wifi'];
$parking = $property['has_parking'];
$isFeatured = $property['is_featured']; 
// Check if the property is saved (variable passed from the dashboard query)
$is_saved = isset($property['is_saved']) && $property['is_saved'] > 0;
?>

<div class="property-card <?= $isFeatured ? 'featured-border' : '' ?>">

    <?php if($isFeatured): ?>
        <span class="featured-badge">FEATURED</span>
    <?php endif; ?>

    <?php include __DIR__ . "/property_gallery.php"; ?>

    <div class="property-info">

        <h3 class="property-title">
            <?= htmlspecialchars($title) ?>
        </h3>

        <p class="property-location">
             <?= htmlspecialchars($town) ?>
        </p>

        <div class="property-price">
            KES <?= number_format($rent) ?>/month
        </div>

        <div class="property-type">
            <?= ucfirst(str_replace("_"," ",$type)) ?>
        </div>

        <div class="property-features">

            <?php if($wifi): ?>
            <span class="feature"><i class="fa-solid fa-wifi"></i> WiFi</span>
            <?php endif; ?>

            <?php if($parking): ?>
            <span class="feature"><i class="fa-solid fa-car"></i> Parking</span>
            <?php endif; ?>

        </div>

        <div class="property-actions" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">

            <a href="view_property.php?id=<?= $property_id ?>" class="btn-view" style="flex-grow: 1; text-align: center; margin-right: 10px;">
                View Details
            </a>

            <form method="POST" action="tenant_dashboard.php" style="margin: 0; display: flex; align-items: center;">
                <input type="hidden" name="property_id" value="<?= $property_id ?>">
                <button type="submit" name="toggle_save" style="background: none; border: none; cursor: pointer; color: #00bcd4; outline: none; padding: 5px; transition: transform 0.2s;">
                    <i class="<?= $is_saved ? 'fa-solid' : 'fa-regular' ?> fa-bookmark" style="font-size: 24px;"></i>
                </button>
            </form>

        </div>

    </div>

</div>