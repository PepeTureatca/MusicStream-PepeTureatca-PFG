<?php
$currentView = $currentView ?? 'all';
$likedSongIds = $likedSongIds ?? [];
$likesCount = $likesCount ?? 0;
$busqueda = $busqueda ?? '';
$userName = $userName ?? 'Usuario';
$sectionTitle = $sectionTitle ?? 'Canciones disponibles';
$canciones = $canciones ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MusicStream - Dashboard</title>
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="assets/css/player.css">
<link rel="stylesheet" href="assets/css/responsiveDashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body data-current-view="<?php echo htmlspecialchars($currentView, ENT_QUOTES, 'UTF-8'); ?>" data-liked-song-ids="<?php echo htmlspecialchars(json_encode($likedSongIds), ENT_QUOTES, 'UTF-8'); ?>">
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon"><i class="fa-solid fa-music"></i></div>
            <span class="logo-text">MusicStream</span>
        </div>

        <nav class="nav-menu">
            <ul>
                <li><a href="dashboard.php" class="<?php echo $currentView === 'all' ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Inicio</a></li>
                <li class="search-toggle">
                    <button class="nav-btn search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="search-text">Buscar</span>
                    </button>
                    <form method="get" action="dashboard.php" class="search-form">
                        <?php if ($currentView === 'likes'): ?>
                            <input type="hidden" name="view" value="likes">
                        <?php endif; ?>
                        <input type="text" name="q" placeholder="Cancion, artista o album..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                </li>
                <li><a href="#"><i class="fa-solid fa-book"></i> Libreria</a></li>
            </ul>
        </nav>

        <div class="nav-divider"></div>

        <nav class="playlist-menu">
            <ul>
                <li><a href="#"><i class="fa-solid fa-plus-square"></i> Crear Lista de Reproduccion</a></li>
                <li><a href="dashboard.php?view=likes" class="<?php echo $currentView === 'likes' ? 'active' : ''; ?>"><i class="fa-solid fa-heart"></i> Favoritos <span class="count likes-count"><?php echo (int) $likesCount; ?></span></a></li>
            </ul>
        </nav>

        <div class="promo-card">
            <p class="promo-title"><i class="fa-solid fa-crown" style="color:#f59e0b;margin-right:6px"></i>MusicStream Premium</p>
            <p class="promo-text">Disfruta de musica sin anuncios y saltos ilimitados.</p>
            <a href="checkout.php" class="promo-btn">Hazte Premium &rarr;</a>
        </div>
    </aside>

    <main class="main-content">
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
                    <a href="cuenta.php" class="dropdown-item">Cuenta</a>
                    <a href="profile.php" class="dropdown-item">Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout">Cerrar sesion</a>
                </div>
            </div>

            <button class="nav-btn hamburger-btn"><i class="fa-solid fa-bars"></i></button>
        </header>

        <h2 class="section-title"><?php echo htmlspecialchars($sectionTitle); ?></h2>

        <div class="grid-container playlist-grid">
    <?php if (empty($canciones)): ?>
        <p class="empty-state-message">No hay canciones en esta vista todavia.</p>
    <?php endif; ?>
    <?php foreach ($canciones as $c): ?>
        <div class="card playlist-card"
            data-song-id="<?php echo (int) $c['id']; ?>"
            data-audio="player.php?id=<?php echo $c['id']; ?>"
            data-title="<?php echo htmlspecialchars($c['title']); ?>"
            data-artist="<?php echo htmlspecialchars($c['artist']); ?>"
            data-cover="image.php?file=<?php echo urlencode(basename($c['cover_url'])); ?>">

            <img src="image.php?file=<?php echo urlencode(basename($c['cover_url'])); ?>" alt="Portada" class="card-image-placeholder">

            <h3 class="card-title"><?php echo htmlspecialchars($c['title']); ?></h3>
            <p class="card-description"><?php echo htmlspecialchars($c['artist']); ?></p>
        </div>
    <?php endforeach; ?>
</div>
    </main>
</div>

<div class="player-bar">
    <div class="song-info">
        <img src="" alt="Portada" class="player-cover-image">
        <div class="song-details">
            <h4 class="song-title">Cancion Actual</h4>
            <p class="artist-name">Artista Desconocido</p>
        </div>
        <button class="btn-icon like-btn" type="button" aria-label="Dar o quitar like" disabled><i class="fa-regular fa-heart"></i></button>
    </div>

    <div class="player-controls">
        <div class="control-buttons">
            <button class="btn-icon"><i class="fa-solid fa-shuffle"></i></button>
            <button class="btn-icon"><i class="fa-solid fa-backward-step"></i></button>
            <button class="btn-icon play-pause"><i class="fa-solid fa-circle-play"></i></button>
            <button class="btn-icon"><i class="fa-solid fa-forward-step"></i></button>
            <button class="btn-icon"><i class="fa-solid fa-repeat"></i></button>
        </div>
        <div class="progress-bar-container">
            <span class="time current-time">0:00</span>
            <div class="progress-bar">
                <div class="progress" style="width: 0%;"></div>
            </div>
            <span class="time total-time">0:00</span>
        </div>
    </div>

    <div class="volume-controls">
        <button class="btn-icon"><i class="fa-solid fa-volume-high"></i></button>
        <div class="volume-bar">
            <div class="volume" style="width: 70%;"></div>
        </div>
    </div>
</div>

<audio id="audio-player"></audio>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/player.js"></script>
</body>
</html>
