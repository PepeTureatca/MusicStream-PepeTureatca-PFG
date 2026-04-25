<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

require_once __DIR__ . '/../app/controller/SongController.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
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

if (!is_readable($audioPath)) {
    http_response_code(500);
    exit('No se puede leer el archivo');
}

$fileSize = filesize($audioPath);
$start = 0;
$end = $fileSize - 1;

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($audioPath) ?: 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Accept-Ranges: bytes');
header('Content-Disposition: inline; filename="' . basename($audioPath) . '"');

if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
        if ($matches[1] !== '') {
            $start = (int) $matches[1];
        }
        if ($matches[2] !== '') {
            $end = (int) $matches[2];
        }

        if ($start > $end || $start >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        $end = min($end, $fileSize - 1);
        $length = $end - $start + 1;

        header('HTTP/1.1 206 Partial Content');
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $length);

        $fp = fopen($audioPath, 'rb');
        fseek($fp, $start);
        $buffer = 8192;
        while (!feof($fp) && ftell($fp) <= $end) {
            $bytesToRead = min($buffer, $end - ftell($fp) + 1);
            echo fread($fp, $bytesToRead);
            flush();
        }
        fclose($fp);
        exit;
    }
}

header('Content-Length: ' . $fileSize);
readfile($audioPath);
exit;