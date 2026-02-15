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
            $datos['titulo'],
            $datos['artista'],
            $datos['album'],
            $datos['duracion'],
            $datos['genero'],
            $datos['audio_url'],
            $datos['cover_url']
        );
    }
}