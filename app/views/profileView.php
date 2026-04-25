<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Perfil</title>

    <link rel="stylesheet" href="/mi-spotify/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/mi-spotify/public/assets/css/responsiveDashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .profile-wrapper {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 24px 100px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 40px;
        }
        .profile-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--text-secondary);
            flex-shrink: 0;
        }
        .profile-meta h2 {
            font-size: 1.6rem;
            font-weight: 700;
        }
        .profile-meta p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 4px;
        }
        .profile-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 24px;
        }
        .profile-section h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: #2a2a2a;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        .form-group input[readonly] {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .btn-save {
            background: var(--accent-color);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-save:hover { opacity: 0.85; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error   { background: #3b1a1a; color: #f87171; border: 1px solid #7f1d1d; }
        .alert-success { background: #1a3b2a; color: #6ee7b7; border: 1px solid #065f46; }
    </style>
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
                    <a href="/mi-spotify/public/cuenta.php" class="dropdown-item">Cuenta</a>
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

            <!-- Editar identidad de perfil -->
            <div class="profile-section">
                <h3>Perfil público</h3>
                <form method="post" action="/mi-spotify/public/profile.php?action=updateProfile">
                    <div class="form-group">
                        <label for="name">Nombre de usuario</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <button type="submit" class="btn-save">Guardar cambios</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
