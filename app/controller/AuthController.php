<?php
require_once __DIR__ . '/../model/userModel.php';

class AuthController
{

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
            session_start();
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
        if (User::encontrarPorEmail($email)) {
            return "Ese correo electrónico ya está registrado";
        }

        $userId = User::crearUsuario($name, $email, $password);
        if ($userId) {
            // Logueamos automáticamente al usuario tras registrarse
            session_start();
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
        if (trim($adminKey) !== self::obtenerClaveAdminEsperada()) {
            return "Clave de alta admin incorrecta";
        }

        if (User::encontrarPorEmail($email)) {
            return "Ese correo electrónico ya está registrado";
        }

        $userId = User::crearAdmin($name, $email, $password);
        if ($userId) {
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'admin';
            header("Location: /mi-spotify/public/dashboardAdmin.php");
            exit;
        }

        return "No se pudo crear el admin. Revisa permisos de BD y vuelve a intentarlo.";
    }
}