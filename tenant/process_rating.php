<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->getConnection();

    $property_id = $_POST['property_id'];
    $tenant_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'] ?? '';

    
    $stmt = $conn->prepare("SELECT landlord_id FROM properties WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $landlord_id = $stmt->fetchColumn();

    $insert = $conn->prepare("INSERT INTO reviews (property_id, tenant_id, landlord_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$property_id, $tenant_id, $landlord_id, $rating, $review_text]);

    header("Location: view_property.php?id=" . $property_id . "&success=rated");
    exit();
}