<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MusicStream - Dashboard Admin</title>
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="assets/css/dashboardadmin.css">
<link rel="stylesheet" href="assets/css/player.css">
<link rel="stylesheet" href="assets/css/responsiveDashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-dashboard">
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <span class="logo-text">Admin Panel</span>
        </div>

        <nav class="nav-menu">
            <ul>
                <li><a href="#" class="active"><i class="fa-solid fa-house"></i> Panel Admin</a></li>
                <li class="search-toggle">
                    <button class="nav-btn search-btn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span class="search-text">Buscar</span>
                    </button>
                    <form method="get" action="dashboardAdmin.php" class="search-form">
                        <input type="text" name="q" placeholder="Cancion, artista o album..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                </li>
            </ul>
        </nav>

        <div class="nav-divider"></div>

        <nav class="playlist-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fa-solid fa-user"></i> Ver dashboard usuario</a></li>
            </ul>
        </nav>

        <div class="promo-card">
            <p class="promo-title">Modo Administrador</p>
            <p class="promo-text">Gestiona canciones, metadatos y portadas desde este panel.</p>
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
                    <div class="user-avatar"><i class="fa-solid fa-user-shield"></i></div>
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

        <div class="admin-title-row">
            <h2 class="section-title">Gestion de canciones</h2>
            <span class="admin-badge">Admin Mode</span>
        </div>

        <section class="admin-upload-panel">
            <h3>Subir cancion (audio + portada)</h3>
            <p class="admin-upload-hint">Arrastra o selecciona archivos, y completa manualmente los metadatos solo si hace falta.</p>

            <?php if ($limitsTooLow): ?>
                <div class="upload-feedback is-error">
                    PHP sigue con limites bajos: upload_max_filesize=<?php echo htmlspecialchars($uploadMax); ?>, post_max_size=<?php echo htmlspecialchars($postMax); ?>.
                </div>
            <?php endif; ?>

            <?php if ($uploadFeedback): ?>
                <div class="upload-feedback <?php echo $uploadFeedback['ok'] ? 'is-success' : 'is-error'; ?>">
                    <?php echo htmlspecialchars($uploadFeedback['message']); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="dashboardAdmin.php" enctype="multipart/form-data" class="upload-form-grid">
                <input type="hidden" name="action" value="upload_song">

                <label>
                    Audio (mp3/wav/flac/ogg/m4a)
                    <input type="file" name="audio_file" accept="audio/*" required>
                </label>

                <label>
                    Portada (jpg/png/webp)
                    <input type="file" name="cover_file" accept="image/*" required>
                </label>

                <label>
                    Titulo (manual opcional)
                    <input type="text" name="title" placeholder="Se usa metadata o nombre de archivo si queda vacio">
                </label>

                <label>
                    Artista (manual opcional)
                    <input type="text" name="artist" placeholder="Se usa metadata si existe">
                </label>

                <label>
                    Album (manual opcional)
                    <input type="text" name="album" placeholder="Album">
                </label>

                <label>
                    Genero (manual opcional)
                    <input type="text" name="genre" placeholder="Genero">
                </label>

                <label>
                    Duracion en segundos (fallback)
                    <input type="number" step="0.01" min="0" name="duration" placeholder="Ej: 210.5">
                </label>

                <button type="submit" class="upload-submit-btn">Subir cancion</button>
            </form>
        </section>

        <div class="admin-title-row">
            <h2 class="section-title">Canciones disponibles</h2>
        </div>
        <div class="grid-container playlist-grid">
            <?php foreach ($canciones as $c): ?>
                <div class="card playlist-card"
                    data-audio="player.php?id=<?php echo $c['id']; ?>"
                    data-title="<?php echo htmlspecialchars($c['title']); ?>"
                    data-artist="<?php echo htmlspecialchars($c['artist']); ?>"
                    data-cover="image.php?file=<?php echo urlencode(basename($c['cover_url'])); ?>">
                    <img src="image.php?file=<?php echo urlencode(basename($c['cover_url'])); ?>" alt="Portada" class="card-image-placeholder">
                    <h3 class="card-title"><?php echo htmlspecialchars($c['title']); ?></h3>
                    <p class="card-description"><?php echo htmlspecialchars($c['artist']); ?></p>
                    <form method="post" action="dashboardAdmin.php" class="admin-song-actions" onsubmit="return confirm('¿Seguro que quieres eliminar esta canción?');">
                        <input type="hidden" name="action" value="delete_song">
                        <input type="hidden" name="song_id" value="<?php echo (int) $c['id']; ?>">
                        <button type="submit" class="admin-delete-btn">Eliminar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<div class="player-bar">
    <div class="song-info">
        <img src="image.php?file=placeholder.png" alt="Portada" class="player-cover-image">
        <div class="song-details">
            <h4 class="song-title">Cancion Actual</h4>
            <p class="artist-name">Artista Desconocido</p>
        </div>
        <button class="btn-icon like-btn"><i class="fa-regular fa-heart"></i></button>
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
