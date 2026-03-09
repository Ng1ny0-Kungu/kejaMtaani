<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$db = (new Database())->getConnection();

try {
    $db->beginTransaction();

   
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    
    $getProps = $db->prepare("SELECT property_id FROM properties WHERE landlord_id = ?");
    $getProps->execute([$user_id]);
    $property_ids = $getProps->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($property_ids)) {
        $placeholders = implode(',', array_fill(0, count($property_ids), '?'));
        
        
        $delMedia = $db->prepare("DELETE FROM property_media WHERE property_id IN ($placeholders)");
        $delMedia->execute($property_ids);

        
        $delSaved = $db->prepare("DELETE FROM saved_properties WHERE property_id IN ($placeholders)");
        $delSaved->execute($property_ids);
        
        
        $delReviews = $db->prepare("DELETE FROM reviews WHERE property_id IN ($placeholders)");
        $delReviews->execute($property_ids);
    }

    $delDocs = $db->prepare("DELETE FROM verification_documents WHERE landlord_id = ?");
    $delDocs->execute([$user_id]);

    
    $delProfile = $db->prepare("DELETE FROM landlord_profiles WHERE user_id = ?");
    $delProfile->execute([$user_id]);

    
    $delProps = $db->prepare("DELETE FROM properties WHERE landlord_id = ?");
    $delProps->execute([$user_id]);

    
    $delUser = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $delUser->execute([$user_id]);

    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    $db->commit();

    // 9. Clear Session
    session_unset();
    session_destroy();

    // Redirect to welcome page
    header("Location: ../../welcome.php?status=account_deleted");
    exit;

} catch (Exception $e) {
    $db->rollBack();
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    die("Error: Could not completely delete account. " . $e->getMessage());
}