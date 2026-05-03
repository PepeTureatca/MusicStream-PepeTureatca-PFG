<?php
require_once __DIR__ . '/db.php';

class Song
{
    // Obtener todas las canciones
    public static function obtenerTodas()
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM songs ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener una canción por ID
    public static function obtenerPorId($id)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Buscar canciones por título, artista o álbum
    public static function buscar($query)
    {
        $conn = conn();
        $likeQuery = "%" . $query . "%";
        $stmt = $conn->prepare("SELECT * FROM songs 
                                WHERE title LIKE ? OR artist LIKE ? OR album LIKE ? 
                                ORDER BY created_at DESC");
        $stmt->bind_param("sss", $likeQuery, $likeQuery, $likeQuery);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerIdsLikePorUsuario(int $userId)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT song_id FROM likes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int) $row['song_id'];
        }

        return $ids;
    }

    public static function buscarFavoritasPorUsuario(int $userId, string $query)
    {
        $conn = conn();
        $query = trim($query);
        if ($query === '') {
            $stmt = $conn->prepare(
                "SELECT s.*
                 FROM songs s
                 INNER JOIN likes l ON l.song_id = s.id
                 WHERE l.user_id = ?
                 ORDER BY l.created_at DESC"
            );
            $stmt->bind_param("i", $userId);
        } else {
            $likeQuery = '%' . $query . '%';
            $stmt = $conn->prepare(
                "SELECT s.*
                 FROM songs s
                 INNER JOIN likes l ON l.song_id = s.id
                 WHERE l.user_id = ?
                   AND (s.title LIKE ? OR s.artist LIKE ? OR s.album LIKE ?)
                 ORDER BY l.created_at DESC"
            );
            $stmt->bind_param("isss", $userId, $likeQuery, $likeQuery, $likeQuery);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function obtenerFavoritasPorUsuario(int $userId)
    {
        return self::buscarFavoritasPorUsuario($userId, '');
    }

    public static function contarLikesPorUsuario(int $userId)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM likes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    private static function usuarioTieneLike(int $userId, int $songId)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND song_id = ?");
        $stmt->bind_param("ii", $userId, $songId);
        $stmt->execute();
        return (bool) $stmt->get_result()->fetch_assoc();
    }

    public static function alternarLike(int $userId, int $songId)
    {
        $conn = conn();
        if (self::usuarioTieneLike($userId, $songId)) {
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND song_id = ?");
            $stmt->bind_param("ii", $userId, $songId);
            $ok = $stmt->execute();

            return [
                'ok' => $ok,
                'liked' => false,
                'count' => self::contarLikesPorUsuario($userId),
                'error' => $ok ? null : $stmt->error,
            ];
        }

        $stmt = $conn->prepare("INSERT INTO likes (user_id, song_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $songId);
        $ok = $stmt->execute();

        return [
            'ok' => $ok,
            'liked' => $ok,
            'count' => self::contarLikesPorUsuario($userId),
            'error' => $ok ? null : $stmt->error,
        ];
    }

    // Crear una nueva canción
    public static function crear($titulo, $artista, $album, $duracion, $genero, $audio_url, $cover_url)
    {
        $result = self::crearConResultado($titulo, $artista, $album, $duracion, $genero, $audio_url, $cover_url);
        return $result['ok'] ? $result['id'] : false;
    }

    // Crear canción con detalle de error para depuración/controlador
    public static function crearConResultado($titulo, $artista, $album, $duracion, $genero, $audio_url, $cover_url)
    {
        $conn = conn();
        $stmt = $conn->prepare("INSERT INTO songs (title, artist, album, duration, genre, audio_url, cover_url, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        if (!$stmt) {
            return [
                'ok' => false,
                'id' => null,
                'error' => 'Prepare failed: ' . $conn->error,
            ];
        }

        $stmt->bind_param("sssdsss", $titulo, $artista, $album, $duracion, $genero, $audio_url, $cover_url);

        if (!$stmt->execute()) {
            return [
                'ok' => false,
                'id' => null,
                'error' => 'Execute failed: ' . $stmt->error,
            ];
        }

        return [
            'ok' => true,
            'id' => $conn->insert_id,
            'error' => null,
        ];
    }

    // Eliminar una canción por ID
    public static function eliminarPorId($id)
    {
        $conn = conn();
        $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");

        if (!$stmt) {
            return [
                'ok' => false,
                'error' => 'Prepare failed: ' . $conn->error,
                'deleted_rows' => 0,
            ];
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            return [
                'ok' => false,
                'error' => 'Execute failed: ' . $stmt->error,
                'deleted_rows' => 0,
            ];
        }

        return [
            'ok' => true,
            'error' => null,
            'deleted_rows' => $stmt->affected_rows,
        ];
    }
}