<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../app/services/googleClient.php';
require_once __DIR__ . '/../app/model/userModel.php';

$client = GoogleClientService::getClient();

if (isset($_GET['code'])) {
    try {
        // Obtenemos el token con el código
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Verificamos si hay error
        if (isset($token['error'])) {
            throw new Exception("Error fetching Google token: " . $token['error']);
        }

        // Verificamos que exista id_token
        $idToken = $token['id_token'] ?? null;
        if (!$idToken) {
            throw new Exception("id_token no recibido de Google");
        }

        // Verificamos el id_token y obtenemos info del usuario
        $googleUser = $client->verifyIdToken($idToken);

        if ($googleUser) {
            $email = $googleUser['email'];
            $name = $googleUser['name'];
            $googleId = $googleUser['sub'];

            // Verificamos si el usuario ya existe
            $user = User::encontrarPorEmail($email);
            if (!$user) {
                // Creamos usuario Google
                $userId = User::crearUsuarioGoogle($name, $email, $googleId);
            } else {
                $userId = $user['id'];
            }

            // Iniciamos sesión
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;

            // Redirigimos al dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            throw new Exception("No se pudo verificar el id_token");
        }
    } catch (Exception $e) {
        echo "Error en Google Auth: " . $e->getMessage();
    }
} else {
    echo "No se recibió código de Google";
}