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