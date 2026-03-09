<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php?role=admin");
    exit;
}

if (!isset($_POST['property_id'])) {
    die("Property ID missing.");
}

$property_id = $_POST['property_id'];

$db = (new Database())->getConnection();



$stmt = $db->prepare("
UPDATE properties
SET moderation_status = 'rejected',
    status = 'draft'
WHERE property_id = ?
");

$stmt->execute([$property_id]);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;