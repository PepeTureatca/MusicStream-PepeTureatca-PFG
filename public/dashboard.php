<?php
session_start();

// Seguridad: si el usuario no tiene sesión, fuera
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/controller/SongController.php';

$userName = $_SESSION['user_name'] ?? 'Usuario';

// Obtener búsqueda si existe
$busqueda = $_GET['q'] ?? '';
$canciones = $busqueda ? SongController::buscar($busqueda) : SongController::listarTodas();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/responsiveDashboard.css">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- JS -->
    <script defer src="assets/js/player.js"></script>
    <script defer src="assets/js/searchToggle.js"></script>
    <script defer src="assets/js/dashboard.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-container">
                <div class="logo-icon"><i class="fa-solid fa-music"></i></div>
                <span class="logo-text">MusicStream</span>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li><a href="#" class="active"><i class="fa-solid fa-house"></i> Inicio</a></li>

                    <li class="search-toggle">
                        <button class="nav-btn search-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span class="search-text">Buscar</span>
                        </button>
                        <form method="get" action="dashboard.php" class="search-form">
                            <input type="text" name="q" placeholder="Canción, artista o álbum..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </li>

                    <li><a href="#"><i class="fa-solid fa-book"></i> Librería</a></li>
                </ul>
            </nav>

            <div class="nav-divider"></div>

            <nav class="playlist-menu">
                <ul>
                    <li><a href="#"><i class="fa-solid fa-plus-square"></i> Crear Lista de Reproducción</a></li>
                    <li><a href="#"><i class="fa-solid fa-heart"></i> Favoritos <span class="count">0</span></a></li>
                </ul>
            </nav>

            <div class="promo-card">
                <p class="promo-title">MusicStream Premium</p>
                <p class="promo-text">Disfruta de música sin anuncios y saltos ilimitados.</p>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <!-- Top bar -->
            <header class="top-bar">
                <div class="history-nav">
                    <button class="nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                </div>

                <div class="user-menu-container">
                    <div class="user-pill">
                        <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                        <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <i class="fa-solid fa-caret-down"></i>
                    </div>

                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">Cuenta</a>
                        <a href="#" class="dropdown-item">Perfil</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout">Cerrar sesión</a>
                    </div>
                </div>

                <!-- Botón hamburguesa solo visible en móvil -->
                <button class="nav-btn hamburger-btn"><i class="fa-solid fa-bars"></i></button>
            </header>

            <h2 class="section-title">Canciones disponibles</h2>

            <div class="grid-container playlist-grid">
                <?php foreach ($canciones as $c): ?>
                    <div class="card playlist-card"
                        data-audio="<?php echo htmlspecialchars($c['audio_url']); ?>"
                        data-title="<?php echo htmlspecialchars($c['title']); ?>"
                        data-artist="<?php echo htmlspecialchars($c['artist']); ?>">
                        <img src="<?php echo htmlspecialchars($c['cover_url']); ?>" alt="Portada" class="card-image-placeholder">
                        <h3 class="card-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars($c['artist']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Player bar -->
    <div class="player-bar">
        <div class="song-info">
            <div class="song-image-placeholder"></div>
            <div class="song-details">
                <h4 class="song-title">Canción Actual</h4>
                <p class="artist-name">Artista Desconocido</p>
            </div>
            <button class="btn-icon like-btn"><i class="fa-regular fa-heart"></i></button>
        </div>

        <div class="player-controls">
            <div class="control-buttons">
                <button class="btn-icon"><i class="fa-solid fa-shuffle"></i></button>
                <button class="btn-icon"><i class="fa-solid fa-backward-step"></i></button>
                <button class="btn-icon play-pause"><i class="fa-solid fa-circle-pause"></i></button>
                <button class="btn-icon"><i class="fa-solid fa-forward-step"></i></button>
                <button class="btn-icon"><i class="fa-solid fa-repeat"></i></button>
            </div>
            <div class="progress-bar-container">
                <span class="time current-time">0:00</span>
                <div class="progress-bar">
                    <div class="progress" style="width: 0%;"></div>
                </div>
                <span class="time total-time">3:45</span>
            </div>
        </div>

        <div class="volume-controls">
            <button class="btn-icon"><i class="fa-solid fa-volume-high"></i></button>
            <div class="volume-bar">
                <div class="volume" style="width: 70%;"></div>
            </div>
        </div>
    </div>
</body>
</html>