<?php
require_once __DIR__ . '/../app/controller/AuthController.php';

session_start();
$error = '';

if (isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin')) {
    header("Location: dashboardAdmin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $adminKey = $_POST['admin_key'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $adminKey === '') {
        $error = 'Rellena todos los campos.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        $error = AuthController::registerAdmin($name, $email, $password, $adminKey);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Alta Admin</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="main-container">

        <div class="header">
            <div class="logo-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1>Alta de Administrador</h1>
            <p>Crea una cuenta con permisos de administración</p>
        </div>

        <div class="card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" placeholder="Nombre del admin" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="admin@ejemplo.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="admin_key">Clave de alta admin</label>
                    <input type="password" id="admin_key" name="admin_key" placeholder="Clave secreta" required>
                </div>

                <button type="submit" class="btn-primary">Crear admin</button>

                <?php if ($error): ?>
                    <p style="color:red; margin-top:10px;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <div class="login-link">
                    <a href="iniciarSesion.php">Volver a iniciar sesión</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>