<?php
require_once __DIR__ . '/db.php';

class Playlist
{
    public static function obtenerPorUsuario(int $userId): array
    {
        $conn = conn();
        $stmt = $conn->prepare(
            "SELECT p.*, COUNT(ps.id) AS song_count
             FROM playlists p
             LEFT JOIN playlist_songs ps ON ps.playlist_id = p.id
             WHERE p.user_id = ?
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function crear(int $userId, string $name, string $description = ''): int|false
    {
        $conn = conn();
        $stmt = $conn->prepare("INSERT INTO playlists (user_id, name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $name, $description);
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        return false;
    }

    public static function eliminar(int $playlistId, int $userId): bool
    {
        $conn = conn();
        $stmt = $conn->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $playlistId, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public static function obtenerPorId(int $id): ?array
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM playlists WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public static function obtenerCanciones(int $playlistId): array
    {
        $conn = conn();
        $stmt = $conn->prepare(
            "SELECT s.*, ps.position
             FROM songs s
             INNER JOIN playlist_songs ps ON ps.song_id = s.id
             WHERE ps.playlist_id = ?
             ORDER BY ps.position ASC, ps.id ASC"
        );
        $stmt->bind_param("i", $playlistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function esDelUsuario(int $playlistId, int $userId): bool
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $playlistId, $userId);
        $stmt->execute();
        return (bool) $stmt->get_result()->fetch_assoc();
    }

    public static function añadirCancion(int $playlistId, int $songId, int $userId): array
    {
        if (!self::esDelUsuario($playlistId, $userId)) {
            return ['ok' => false, 'message' => 'No tienes permiso para modificar esta lista.'];
        }

        $conn = conn();

        $dup = $conn->prepare("SELECT id FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
        $dup->bind_param("ii", $playlistId, $songId);
        $dup->execute();
        if ($dup->get_result()->fetch_assoc()) {
            return ['ok' => false, 'message' => 'La cancion ya esta en esta lista.'];
        }

        $posStmt = $conn->prepare(
            "SELECT COALESCE(MAX(position), 0) + 1 AS next_pos FROM playlist_songs WHERE playlist_id = ?"
        );
        $posStmt->bind_param("i", $playlistId);
        $posStmt->execute();
        $pos = (int) ($posStmt->get_result()->fetch_assoc()['next_pos'] ?? 1);

        $ins = $conn->prepare("INSERT INTO playlist_songs (playlist_id, song_id, position) VALUES (?, ?, ?)");
        $ins->bind_param("iii", $playlistId, $songId, $pos);
        if ($ins->execute()) {
            return ['ok' => true, 'message' => 'Cancion añadida.'];
        }
        return ['ok' => false, 'message' => 'No se pudo añadir la cancion.'];
    }

    public static function quitarCancion(int $playlistId, int $songId, int $userId): bool
    {
        if (!self::esDelUsuario($playlistId, $userId)) {
            return false;
        }
        $conn = conn();
        $stmt = $conn->prepare("DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
        $stmt->bind_param("ii", $playlistId, $songId);
        return $stmt->execute();
    }
}
