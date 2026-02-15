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
        $conn = conn();
        $stmt = $conn->prepare("INSERT INTO songs (title, artist, album, duration, genre, audio_url, cover_url, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdsss", $titulo, $artista, $album, $duracion, $genero, $audio_url, $cover_url);
        return $stmt->execute() ? $conn->insert_id : false;
    }
}