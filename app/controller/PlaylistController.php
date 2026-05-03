<?php
require_once __DIR__ . '/../model/playlistModel.php';
require_once __DIR__ . '/../model/songModel.php';

class PlaylistController
{
    public static function handleAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['ok' => false, 'message' => 'No autenticado.']);
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $name = trim($_POST['name'] ?? '');
                $desc = trim($_POST['description'] ?? '');

                if ($name === '') {
                    echo json_encode(['ok' => false, 'message' => 'El nombre es obligatorio.']);
                    exit;
                }
                if (mb_strlen($name) > 100) {
                    echo json_encode(['ok' => false, 'message' => 'El nombre no puede superar 100 caracteres.']);
                    exit;
                }

                $id = Playlist::crear($userId, $name, $desc);
                echo json_encode(
                    $id
                        ? ['ok' => true, 'id' => $id, 'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8')]
                        : ['ok' => false, 'message' => 'No se pudo crear la lista.']
                );
                break;

            case 'delete':
                $playlistId = (int) ($_POST['playlist_id'] ?? 0);
                $ok = Playlist::eliminar($playlistId, $userId);
                echo json_encode(['ok' => $ok, 'message' => $ok ? 'Lista eliminada.' : 'No se pudo eliminar.']);
                break;

            case 'add_song':
                $playlistId = (int) ($_POST['playlist_id'] ?? 0);
                $songId     = (int) ($_POST['song_id'] ?? 0);
                echo json_encode(Playlist::añadirCancion($playlistId, $songId, $userId));
                break;

            case 'remove_song':
                $playlistId = (int) ($_POST['playlist_id'] ?? 0);
                $songId     = (int) ($_POST['song_id'] ?? 0);
                $ok = Playlist::quitarCancion($playlistId, $songId, $userId);
                echo json_encode(['ok' => $ok]);
                break;

            default:
                echo json_encode(['ok' => false, 'message' => 'Accion desconocida.']);
        }
        exit;
    }

    public static function showPlaylist(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $userId     = (int) $_SESSION['user_id'];
        $playlistId = (int) ($_GET['id'] ?? 0);

        $currentPlaylist = $playlistId > 0 ? Playlist::obtenerPorId($playlistId) : null;

        if (!$currentPlaylist || (int) $currentPlaylist['user_id'] !== $userId) {
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        }

        $canciones    = Playlist::obtenerCanciones($playlistId);
        $playlists    = Playlist::obtenerPorUsuario($userId);
        $likedSongIds = Song::obtenerIdsLikePorUsuario($userId);
        $likesCount   = count($likedSongIds);
        $userName     = $_SESSION['user_name'] ?? 'Usuario';

        require_once __DIR__ . '/../views/playlistView.php';
    }
}
