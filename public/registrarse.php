<?php
require_once __DIR__ . '/../app/controller/AuthController.php';

session_start();
$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? 'Usuario'; // default si no pones nombre
    $email = $_POST['email'];
    $password = $_POST['password'];

    $error = AuthController::register($name, $email, $password);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Crear Cuenta</title>
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="main-container">

        <div class="header">
            <div class="logo-icon">
                <i class="fa-solid fa-music"></i>
            </div>
            <h1>MusicStream</h1>
            <p>Crea tu cuenta</p>
        </div>

        <div class="card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" placeholder="Tu nombre" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Introduce tu contraseña" required>
                </div>

                <button type="submit" class="btn-primary">Registrarse</button>

                <?php if ($error): ?>
                    <p style="color:red; margin-top:10px;"><?php echo $error; ?></p>
                <?php endif; ?>

                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="iniciarSesion.php">Inicia sesión</a>
                </div>

                <div class="divider"></div>

                <?php
                require_once __DIR__ . '/../app/services/GoogleClient.php';
                $googleLoginUrl = GoogleClientService::getClient()->createAuthUrl();
                ?>

                <!-- Botón de Google -->
                <a href="<?php echo $googleLoginUrl; ?>" class="btn-google">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>
                    Continuar con Google
                </a>
            </form>

            <p class="terms">
                Al continuar, aceptas los Términos de Servicio y la Política de Privacidad de MusicStream.
            </p>
        </div>
    </div>

</body>

</html>