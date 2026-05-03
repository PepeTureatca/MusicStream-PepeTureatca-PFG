<?php
$file = $_GET['file'] ?? '';

$path = __DIR__ . '/../uploads/artCover/' . basename($file);

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/jpeg');
readfile($path);