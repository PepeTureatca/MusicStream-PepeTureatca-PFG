<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Perfil</title>

    <link rel="stylesheet" href="/mi-spotify/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/mi-spotify/public/assets/css/responsiveDashboard.css">
    <link rel="stylesheet" href="/mi-spotify/public/assets/css/profileView.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li><a href="/mi-spotify/public/dashboard.php"><i class="fa-solid fa-house"></i> Inicio</a></li>
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
    </aside>

    <!-- Main content -->
    <main class="main-content" style="overflow-y: auto;">
        <header class="top-bar">
            <div class="history-nav">
                <a href="/mi-spotify/public/dashboard.php" class="nav-btn" style="text-decoration:none;">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            </div>
            <div class="user-menu-container">
                <div class="user-pill">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    <i class="fa-solid fa-caret-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">Cuenta</a>
                    <a href="/mi-spotify/public/profile.php" class="dropdown-item">Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a href="/mi-spotify/public/logout.php" class="dropdown-item logout">Cerrar sesión</a>
                </div>
            </div>
        </header>

        <div class="profile-wrapper">
            <!-- Cabecera del perfil -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="profile-meta">
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['google_id'])): ?>
                        <p><i class="fa-brands fa-google" style="color:#ea4335;"></i> Cuenta vinculada con Google</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Editar información básica -->
            <div class="profile-section">
                <h3>Información de la cuenta</h3>
                <form method="post" action="/mi-spotify/public/profile.php?action=update">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            <?php echo !empty($user['google_id']) ? 'readonly title="No puedes cambiar el email de una cuenta de Google"' : ''; ?> required>
                    </div>
                    <button type="submit" class="btn-save">Guardar cambios</button>
                </form>
            </div>

            <!-- Cambiar contraseña (solo si no es cuenta Google) -->
            <?php if (empty($user['google_id'])): ?>
            <div class="profile-section">
                <h3>Cambiar contraseña</h3>
                <form method="post" action="/mi-spotify/public/profile.php?action=updatePassword">
                    <div class="form-group">
                        <label for="current_password">Contraseña actual</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar nueva contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn-save">Cambiar contraseña</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
