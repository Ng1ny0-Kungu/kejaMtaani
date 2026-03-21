<?php

$imageQuery = $conn->prepare("
    SELECT file_path 
    FROM property_media 
    WHERE property_id = ? 
    AND media_type = 'image' 
    AND is_active = 1 
    ORDER BY is_primary DESC, upload_order ASC 
    LIMIT 1
");

$imageQuery->execute([$property_id]);
$row = $imageQuery->fetch();


$imagePath = "../assets/images/default_property.jpg"; 

if ($row) {
    
    $imagePath = "../" . $row['file_path'];
}
?>

<div class="property-gallery">
    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Property Image" style="width:100%; height:200px; object-fit:cover;">
</div>