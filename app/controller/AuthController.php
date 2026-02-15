<?php
require_once __DIR__ . '/../model/userModel.php';

class authController
{

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
            header("Location: /mi-spotify/public/dashboard.php");
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
            header("Location: /mi-spotify/public/dashboard.php");
            exit;
        } else {
            return "Error al crear la cuenta, inténtalo de nuevo";
        }
    }
}