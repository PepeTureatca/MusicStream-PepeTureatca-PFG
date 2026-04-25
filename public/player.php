<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../app/controller/SongController.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit('ID no válido');
}

$cancion = SongController::mostrar($id);
if (!$cancion) {
    http_response_code(404);
    exit('Canción no encontrada');
}

$audioPath = __DIR__ . '/../uploads/music/' . basename($cancion['audio_url']);
if (!file_exists($audioPath)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

$fileSize = filesize($audioPath);
$start = 0;
$end = $fileSize - 1;

header('Content-Type: audio/mpeg');
header('Accept-Ranges: bytes');
header('Content-Disposition: inline; filename="' . basename($audioPath) . '"');

if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
    $start = (int)$matches[1];
    $end = $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;

    http_response_code(206);
    header("Content-Range: bytes $start-$end/$fileSize");
    header('Content-Length: ' . ($end - $start + 1));
} else {
    header('Content-Length: ' . $fileSize);
}

$fp = fopen($audioPath, 'rb');
fseek($fp, $start);
$remaining = $end - $start + 1;
while ($remaining > 0 && !feof($fp)) {
    $chunk = fread($fp, min(8192, $remaining));
    echo $chunk;
    $remaining -= strlen($chunk);
}
fclose($fp);
exit;