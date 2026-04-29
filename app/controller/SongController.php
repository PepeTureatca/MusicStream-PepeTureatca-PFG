<?php
require_once __DIR__ . '/../model/songModel.php';

class SongController
{
    // Devuelve todas las canciones
    public static function listarTodas()
    {
        return Song::obtenerTodas();
    }

    // Devuelve una canción específica por ID
    public static function mostrar($id)
    {
        return Song::obtenerPorId($id);
    }

    // Buscar canciones según la búsqueda del usuario
    public static function buscar($query)
    {
        return Song::buscar($query);
    }

    // Crear nueva canción (opcional)
    public static function crear($datos)
    {
        return Song::crear(
            $datos['titulo'] ?? $datos['title'] ?? '',
            $datos['artista'] ?? $datos['artist'] ?? '',
            $datos['album'] ?? '',
            $datos['duracion'] ?? $datos['duration'] ?? 0,
            $datos['genero'] ?? $datos['genre'] ?? '',
            $datos['audio_url'],
            $datos['cover_url']
        );
    }

    public static function subirCancion($post, $files)
    {
        if (!isset($files['audio_file'], $files['cover_file'])) {
            return ['ok' => false, 'message' => 'Debes subir audio y portada.'];
        }

        if (($files['audio_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'ok' => false,
                'message' => 'Error al subir el archivo de audio: ' . self::uploadErrorToMessage((int) ($files['audio_file']['error'] ?? UPLOAD_ERR_NO_FILE)),
            ];
        }

        if (($files['cover_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'ok' => false,
                'message' => 'Error al subir la portada: ' . self::uploadErrorToMessage((int) ($files['cover_file']['error'] ?? UPLOAD_ERR_NO_FILE)),
            ];
        }

        $audioInfo = self::validarArchivo($files['audio_file'], ['mp3', 'wav', 'flac', 'ogg', 'm4a']);
        if (!$audioInfo['ok']) {
            return $audioInfo;
        }

        $coverInfo = self::validarArchivo($files['cover_file'], ['jpg', 'jpeg', 'png', 'webp']);
        if (!$coverInfo['ok']) {
            return $coverInfo;
        }

        $projectRoot = dirname(__DIR__, 2);
        $audioDir = $projectRoot . '/uploads/music';
        $coverDir = $projectRoot . '/uploads/artCover';
        if (!is_dir($audioDir)) {
            mkdir($audioDir, 0775, true);
        }
        if (!is_dir($coverDir)) {
            mkdir($coverDir, 0775, true);
        }

        if (!is_dir($audioDir) || !is_writable($audioDir)) {
            return ['ok' => false, 'message' => 'La carpeta uploads/music no existe o no tiene permisos de escritura.'];
        }

        if (!is_dir($coverDir) || !is_writable($coverDir)) {
            return ['ok' => false, 'message' => 'La carpeta uploads/artCover no existe o no tiene permisos de escritura.'];
        }

        $audioFilename = bin2hex(random_bytes(16)) . '.' . $audioInfo['ext'];
        $coverFilename = bin2hex(random_bytes(16)) . '.' . $coverInfo['ext'];
        $audioTarget = $audioDir . '/' . $audioFilename;
        $coverTarget = $coverDir . '/' . $coverFilename;

        if (!is_uploaded_file($files['audio_file']['tmp_name'] ?? '')) {
            return ['ok' => false, 'message' => 'El archivo de audio recibido no es una subida valida.'];
        }

        if (!move_uploaded_file($files['audio_file']['tmp_name'], $audioTarget)) {
            return ['ok' => false, 'message' => 'No se pudo guardar el audio en uploads/music.'];
        }

        if (!is_uploaded_file($files['cover_file']['tmp_name'] ?? '')) {
            @unlink($audioTarget);
            return ['ok' => false, 'message' => 'La portada recibida no es una subida valida.'];
        }

        if (!move_uploaded_file($files['cover_file']['tmp_name'], $coverTarget)) {
            @unlink($audioTarget);
            return ['ok' => false, 'message' => 'No se pudo guardar la portada en uploads/artCover.'];
        }

        $title = trim($post['title'] ?? '');
        $artist = trim($post['artist'] ?? '');
        $album = trim($post['album'] ?? '');
        $genre = trim($post['genre'] ?? '');
        $manualDuration = isset($post['duration']) ? (float) $post['duration'] : 0.0;

        $metadata = self::extraerMetadatos($audioTarget);
        $duration = $metadata['duration'] > 0 ? $metadata['duration'] : $manualDuration;

        if ($title === '') {
            $title = $metadata['title'] !== ''
                ? $metadata['title']
                : pathinfo($files['audio_file']['name'], PATHINFO_FILENAME);
        }

        if ($artist === '') {
            $artist = $metadata['artist'] !== '' ? $metadata['artist'] : 'Artista desconocido';
        }

        if ($duration <= 0) {
            // Si no hay metadatos ni duración manual, guardamos con 0 para no bloquear la subida.
            $duration = 0;
        }

        $insertResult = Song::crearConResultado(
            $title,
            $artist,
            $album,
            $duration,
            $genre,
            'uploads/music/' . $audioFilename,
            'uploads/artCover/' . $coverFilename
        );

        if (!$insertResult['ok']) {
            @unlink($audioTarget);
            @unlink($coverTarget);
            return [
                'ok' => false,
                'message' => 'No se pudo guardar la canción en la base de datos. ' . ($insertResult['error'] ?? ''),
            ];
        }

        return ['ok' => true, 'message' => 'Canción subida correctamente (ID ' . $insertResult['id'] . ').'];
    }

    private static function uploadErrorToMessage($code)
    {
        $limitInfo = ' (upload_max_filesize=' . (ini_get('upload_max_filesize') ?: 'n/a')
            . ', post_max_size=' . (ini_get('post_max_size') ?: 'n/a') . ')';

        switch ($code) {
            case UPLOAD_ERR_OK:
                return 'OK';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo supera el tamaño máximo permitido por PHP' . $limitInfo . '.';
            case UPLOAD_ERR_PARTIAL:
                return 'La subida se completó solo parcialmente.';
            case UPLOAD_ERR_NO_FILE:
                return 'No se recibió archivo.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal de PHP.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'PHP no pudo escribir el archivo en disco.';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP bloqueó la subida.';
            default:
                return 'Error desconocido (código ' . $code . ').';
        }
    }

    private static function validarArchivo($file, $allowedExtensions)
    {
        $originalName = $file['name'] ?? '';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            return ['ok' => false, 'message' => 'Formato no permitido para ' . $originalName . '.'];
        }

        return ['ok' => true, 'ext' => $ext];
    }

    private static function extraerMetadatos($audioPath)
    {
        $result = [
            'title' => '',
            'artist' => '',
            'duration' => 0.0,
        ];

        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($autoload)) {
            return $result;
        }

        require_once $autoload;
        if (!class_exists('getID3')) {
            return $result;
        }

        $getId3Class = 'getID3';
        $analyzer = new $getId3Class();
        $info = $analyzer->analyze($audioPath);

        if (isset($info['playtime_seconds'])) {
            $result['duration'] = round((float) $info['playtime_seconds'], 2);
        }

        if (!empty($info['tags']) && is_array($info['tags'])) {
            $result['title'] = self::firstTag($info['tags'], 'title');
            $result['artist'] = self::firstTag($info['tags'], 'artist');
        }

        return $result;
    }

    private static function firstTag($tags, $field)
    {
        foreach ($tags as $tagSet) {
            if (!empty($tagSet[$field][0])) {
                return trim((string) $tagSet[$field][0]);
            }
        }

        return '';
    }
}