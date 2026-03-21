<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Handle AJAX POST (From view_property.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $prop_id = $_POST['property_id'];

    $check = $db->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $check->execute([$user_id, $prop_id]);

    if ($check->fetch()) {
        $stmt = $db->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $prop_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        $stmt = $db->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $prop_id]);
        echo json_encode(['status' => 'success', 'action' => 'saved']);
    }
    exit;
}

// Handle GET Remove (From saved_properties.php)
if (isset($_GET['remove'])) {
    $prop_id = $_GET['remove'];
    $stmt = $db->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $stmt->execute([$user_id, $prop_id]);
    header("Location: ../saved_properties.php");
    exit;
}