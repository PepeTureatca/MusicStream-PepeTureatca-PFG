<?php
require_once __DIR__ . '/../model/userModel.php';

class AuthController
{

    public static function validarPasswordFuerte($password)
    {
        if (strlen($password) < 8) {
            return "La contraseña debe tener al menos 8 caracteres.";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return "La contraseña debe incluir al menos una letra mayúscula.";
        }

        if (!preg_match('/[0-9]/', $password)) {
            return "La contraseña debe incluir al menos un número.";
        }

        if (strpos($password, '-') === false) {
            return "La contraseña debe incluir al menos un guion (-).";
        }

        return null;
    }

    private static function obtenerClaveAdminEsperada()
    {
        $fromEnv = getenv('ADMIN_REGISTER_KEY');
        if ($fromEnv !== false && trim($fromEnv) !== '') {
            return trim($fromEnv);
        }

        return '180705';
    }

    public static function login($email, $password)
    {
        $user = User::encontrarPorEmail($email);

        if (!$user) {
            return "El correo electrónico no se ha encontrado";
        }

        if (User::verificarPassword($email, $password)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';
            if (($_SESSION['user_role'] ?? 'user') === 'admin') {
                header("Location: /mi-spotify/public/dashboardAdmin.php");
            } else {
                header("Location: /mi-spotify/public/dashboard.php");
            }
            exit;
        } else {
            return "Contraseña incorrecta";
        }
    }

    public static function register($name, $email, $password)
    {
        $passwordError = self::validarPasswordFuerte($password);
        if ($passwordError) {
            return $passwordError;
        }

        if (User::encontrarPorEmail($email)) {
            return "Ese correo electrónico ya está registrado";
        }

        $userId = User::crearUsuario($name, $email, $password);
        if ($userId) {
            // Logueamos automáticamente al usuario tras registrarse
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        } else {
            return "Error al crear la cuenta, inténtalo de nuevo";
        }
    }

    public static function registerAdmin($name, $email, $password, $adminKey)
    {
        $passwordError = self::validarPasswordFuerte($password);
        if ($passwordError) {
            return $passwordError;
        }

        if (trim($adminKey) !== self::obtenerClaveAdminEsperada()) {
            return "Clave de alta admin incorrecta";
        }

        if (User::encontrarPorEmail($email)) {
            return "Ese correo electrónico ya está registrado";
        }

        $userId = User::crearAdmin($name, $email, $password);
        if ($userId) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'admin';
            header("Location: /mi-spotify/public/dashboardAdmin.php");
            exit;
        }

        return "No se pudo crear el admin. Revisa permisos de BD y vuelve a intentarlo.";
    }

    public static function showLogin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['user_id'])) {
            if (($_SESSION['user_role'] ?? 'user') === 'admin') {
                header("Location: /mi-spotify/public/dashboardAdmin.php");
            } else {
                header("Location: /mi-spotify/public/dashboard.php");
            }
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $error = self::login($email, $password) ?? '';
        }

        require_once __DIR__ . '/../services/GoogleClient.php';
        $googleLoginUrl = GoogleClientService::getClient()->createAuthUrl();
        require_once __DIR__ . '/../views/loginView.php';
    }

    public static function showRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? 'Usuario';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $error = self::register($name, $email, $password) ?? '';
        }

        require_once __DIR__ . '/../services/GoogleClient.php';
        $googleLoginUrl = GoogleClientService::getClient()->createAuthUrl();
        require_once __DIR__ . '/../views/registerView.php';
    }

    public static function showAltaAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin')) {
            header("Location: /mi-spotify/public/dashboardAdmin.php");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $adminKey = $_POST['admin_key'] ?? '';

            if ($name === '' || $email === '' || $password === '' || $adminKey === '') {
                $error = 'Rellena todos los campos.';
            } else {
                $error = self::registerAdmin($name, $email, $password, $adminKey) ?? '';
            }
        }

        require_once __DIR__ . '/../views/altaAdminView.php';
    }
}