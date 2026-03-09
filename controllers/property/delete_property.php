<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    die("Unauthorized access.");
}

if (!isset($_GET['id'])) {
    die("Property ID missing.");
}

$property_id = $_GET['id'];
$landlord_id = $_SESSION['user_id'];

$db = (new Database())->getConnection();

try {
    $checkStmt = $db->prepare("SELECT property_id FROM properties WHERE property_id = ? AND landlord_id = ?");
    $checkStmt->execute([$property_id, $landlord_id]);
    $property = $checkStmt->fetch();

    if (!$property) {
        die("Property not found or access denied.");
    }

    $db->beginTransaction();

    $deleteMedia = $db->prepare("DELETE FROM property_media WHERE property_id = ?");
    $deleteMedia->execute([$property_id]);

    $deleteProperty = $db->prepare("DELETE FROM properties WHERE property_id = ? AND landlord_id = ?");
    $deleteProperty->execute([$property_id, $landlord_id]);


    $db->commit();

    // 7. Redirect back with success message
    header("Location: ../../dashboard/landlord_dashboard.php?deleted=1");
    exit;

} catch (Exception $e) {
    // Rollback if something goes wrong
    $db->rollBack();
    die("Error deleting property: " . $e->getMessage());
}