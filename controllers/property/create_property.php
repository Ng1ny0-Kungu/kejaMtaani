<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'landlord') {
    header('Location: ../../auth/login.php?role=landlord');
    exit;
}

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../dashboard/add_property.php");
    exit;
}



$title = trim($_POST['title'] ?? '');
$property_type = $_POST['property_type'] ?? '';
$rent_amount = (int)($_POST['rent_amount'] ?? 0);

$deposit_amount = isset($_POST['deposit_amount']) && $_POST['deposit_amount'] !== ''
    ? (int)$_POST['deposit_amount']
    : NULL;

$electricity_payment = $_POST['electricity_payment'] ?? NULL;
$water_payment = $_POST['water_payment'] ?? NULL;

$county = trim($_POST['county'] ?? '');
$constituency = trim($_POST['constituency'] ?? '');
$ward = trim($_POST['ward'] ?? '');
$locality = trim($_POST['locality'] ?? '');

$latitude = $_POST['latitude'] ?? NULL;
$longitude = $_POST['longitude'] ?? NULL;

/* Feature flags */
$has_wifi = isset($_POST['has_wifi']) ? 1 : 0;
$has_parking = isset($_POST['has_parking']) ? 1 : 0;
$has_security = isset($_POST['has_security']) ? 1 : 0;
$has_balcony = isset($_POST['has_balcony']) ? 1 : 0;
$has_rooftop_access = isset($_POST['has_rooftop_access']) ? 1 : 0;



if (empty($title) || empty($property_type) || $rent_amount <= 0 || empty($county)) {
    die("Required fields missing.");
}



function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

$baseSlug = createSlug($title . '-' . $locality . '-' . $county);
$slug = $baseSlug;
$counter = 1;

while (true) {
    $check = $db->prepare("SELECT property_id FROM properties WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->rowCount() == 0) break;
    $slug = $baseSlug . '-' . $counter++;
}



$stmt = $db->prepare("
INSERT INTO properties
(landlord_id, title, property_type, rent_amount, deposit_amount,
 electricity_payment, water_payment,
 county, constituency, ward, locality,
 latitude, longitude,
 has_wifi, has_parking, has_security,
 has_balcony, has_rooftop_access,
 status, moderation_status, slug, published_at)
VALUES
(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'draft','pending_review',?, NULL)
");

$stmt->execute([
    $_SESSION['user_id'],
    $title,
    $property_type,
    $rent_amount,
    $deposit_amount,
    $electricity_payment,
    $water_payment,
    $county,
    $constituency,
    $ward,
    $locality,
    $latitude,
    $longitude,
    $has_wifi,
    $has_parking,
    $has_security,
    $has_balcony,
    $has_rooftop_access,
    $slug
]);

$property_id = $db->lastInsertId();



$year = date('Y');
$propertyCode = "KJM-" . $year . "-" . str_pad($property_id, 5, "0", STR_PAD_LEFT);

$update = $db->prepare("UPDATE properties SET property_code = ? WHERE property_id = ?");
$update->execute([$propertyCode, $property_id]);



require_once __DIR__ . '/upload_media.php';
uploadMedia($property_id, $db);


header("Location: ../../dashboard/landlord_dashboard.php?success=1");
exit;