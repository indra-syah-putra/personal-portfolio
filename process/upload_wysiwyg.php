<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'No file uploaded']));
}

$result = upload_gambar($_FILES['image']);
if ($result === false) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Invalid file type or upload failed']));
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
$url = $base_url . '/' . $result;

header('Content-Type: application/json');
echo json_encode(['success' => true, 'url' => $url]);
