<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid request');
}

$db = (new Database())->getConnection();
$docId = (int) $_GET['id'];

$stmt = $db->prepare("
    SELECT document_path, document_name
    FROM verification_documents
    WHERE document_id = ?
    LIMIT 1
");
$stmt->execute([$docId]);
$document = $stmt->fetch();

if (!$document) {
    exit('File not found');
}

$file_path = __DIR__ . '/../uploads/verification_docs/' . $document['document_path'];

if (!file_exists($file_path)) {
    exit('File missing on server');
}


$downloadName = $document['document_name'];

if (empty($downloadName)) {
    $parts = explode('_', $document['document_path'], 2);
    $downloadName = $parts[1] ?? $document['document_path'];
}


header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
