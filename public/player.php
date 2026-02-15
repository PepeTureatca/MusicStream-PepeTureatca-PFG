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

// Headers para descargar/reproducir audio
header('Content-Type: audio/mpeg'); // o audio/wav según tu archivo
header('Content-Length: ' . filesize($audioPath));
header('Content-Disposition: inline; filename="' . basename($audioPath) . '"');
readfile($audioPath);
exit;