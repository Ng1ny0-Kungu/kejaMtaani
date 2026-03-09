<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/upload_media.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$db = (new Database())->getConnection();
$property_id = $_POST['property_id'];

// 1. Verify ownership
$stmt = $db->prepare("SELECT * FROM properties WHERE property_id = ? AND landlord_id = ?");
$stmt->execute([$property_id, $_SESSION['user_id']]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Access denied.");
}

// 2. Perform the Update 
// We reset status to 'pending_review' so Admin sees the new changes/images
$update = $db->prepare("
    UPDATE properties
    SET title = ?,
        property_type = ?,
        rent_amount = ?,
        deposit_amount = ?,
        electricity_payment = ?,
        water_payment = ?,
        has_wifi = ?,
        has_balcony = ?,
        has_parking = ?,
        has_rooftop_access = ?,
        moderation_status = 'pending_review' 
    WHERE property_id = ?
");

$update->execute([
    $_POST['title'],
    $_POST['property_type'],
    $_POST['rent_amount'],
    $_POST['deposit_amount'],
    $_POST['electricity_payment'],
    $_POST['water_payment'],
    isset($_POST['has_wifi']) ? 1 : 0,
    isset($_POST['has_balcony']) ? 1 : 0,
    isset($_POST['has_parking']) ? 1 : 0,
    isset($_POST['has_rooftop_access']) ? 1 : 0,
    $property_id
]);

// 3. Handle new media uploads
uploadMedia($property_id, $db);

header("Location: ../../dashboard/landlord_dashboard.php?updated=1");
exit;