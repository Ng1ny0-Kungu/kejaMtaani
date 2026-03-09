<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$db = (new Database())->getConnection();

if (!isset($_GET['id']) || !isset($_GET['property'])) {
    die("Invalid request.");
}

$media_id = $_GET['id'];
$property_id = $_GET['property'];


$stmt = $db->prepare("
SELECT pm.*, p.landlord_id
FROM property_media pm
JOIN properties p ON pm.property_id = p.property_id
WHERE pm.media_id = ?
");
$stmt->execute([$media_id]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$media || $media['landlord_id'] != $_SESSION['user_id']) {
    die("Unauthorized.");
}


$filePath = __DIR__ . '/../../' . $media['file_path'];
if (file_exists($filePath)) {
    unlink($filePath);
}


$delete = $db->prepare("DELETE FROM property_media WHERE media_id = ?");
$delete->execute([$media_id]);

header("Location: ../../dashboard/edit_property.php?id=" . $property_id);
exit;