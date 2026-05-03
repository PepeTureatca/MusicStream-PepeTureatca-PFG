<?php
require_once __DIR__ . '/../model/userModel.php';
require_once __DIR__ . '/AuthController.php';

class UserController
{
    public static function show()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $user = User::obtenerPorId($_SESSION['user_id']);
        if (!$user) {
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        }

        $error = null;
        $success = null;
        require_once __DIR__ . '/../views/profileView.php';
    }

    public static function showAccount()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $user = User::obtenerPorId($_SESSION['user_id']);
        if (!$user) {
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        }

        $error = null;
        $success = null;
        require_once __DIR__ . '/../views/accountView.php';
    }

    public static function update()
    {
        self::updateProfile();
    }

    public static function updateProfile()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $name = trim($_POST['name'] ?? '');

        $error   = null;
        $success = null;

        if (empty($name)) {
            $error = "El nombre es obligatorio.";
        } else {
            $existenteNombre = User::encontrarPorNombre($name);
            if ($existenteNombre && $existenteNombre['id'] != $_SESSION['user_id']) {
                $error = "Ese nombre de usuario ya está en uso.";
            } else {
                $user = User::obtenerPorId($_SESSION['user_id']);
                User::actualizar($_SESSION['user_id'], $name, $user['email']);
                $_SESSION['user_name'] = $name;
                $success = "Perfil actualizado correctamente.";
            }
        }

        $user = User::obtenerPorId($_SESSION['user_id']);
        require_once __DIR__ . '/../views/profileView.php';
    }

    public static function updateAccount()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $error = null;
        $success = null;
        $user = User::obtenerPorId($_SESSION['user_id']);

        if (empty($email)) {
            $error = "El correo es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo no tiene un formato válido.";
        } elseif (!empty($user['google_id'])) {
            $error = "No puedes cambiar el email de una cuenta vinculada con Google.";
        } else {
            $existenteEmail = User::encontrarPorEmail($email);
            if ($existenteEmail && $existenteEmail['id'] != $_SESSION['user_id']) {
                $error = "Ese correo ya está en uso por otra cuenta.";
            } else {
                User::actualizar($_SESSION['user_id'], $user['name'], $email);
                $success = "Cuenta actualizada correctamente.";
            }
        }

        $user = User::obtenerPorId($_SESSION['user_id']);
        require_once __DIR__ . '/../views/accountView.php';
    }

    public static function updatePassword()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $actual    = $_POST['current_password'] ?? '';
        $nueva     = $_POST['new_password']     ?? '';
        $confirmar = $_POST['confirm_password'] ?? '';

        $error   = null;
        $success = null;
        $user    = User::obtenerPorId($_SESSION['user_id']);

        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            $error = "Rellena todos los campos de contraseña.";
        } elseif ($nueva !== $confirmar) {
            $error = "La nueva contraseña y la confirmación no coinciden.";
        } elseif ($passwordError = AuthController::validarPasswordFuerte($nueva)) {
            $error = $passwordError;
        } elseif (!User::verificarPassword($user['email'], $actual)) {
            $error = "La contraseña actual es incorrecta.";
        } else {
            User::actualizarPassword($_SESSION['user_id'], $nueva);
            $success = "Contraseña actualizada correctamente.";
        }

        require_once __DIR__ . '/../views/accountView.php';
    }
}
