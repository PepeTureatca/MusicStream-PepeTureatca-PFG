<?php
$file = $_GET['file'] ?? '';

$path = __DIR__ . '/../uploads/artCover/' . basename($file);

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
header('Content-Type: ' . $mime);
readfile($path);