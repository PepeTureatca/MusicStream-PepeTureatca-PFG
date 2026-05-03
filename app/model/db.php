<?php
require_once __DIR__ . '/../../bootstrap.php';

function conn()
{
    if (getenv('DB_ENV') === 'prod') {
        $host = getenv('DB_HOST_PROD');
        $user = getenv('DB_USER_PROD');
        $pass = getenv('DB_PASS_PROD');
        $db   = getenv('DB_NAME_PROD');
    } else {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $db   = getenv('DB_NAME') ?: 'mi_spotify_tfg';
    }

    $port = (int) (getenv('DB_PORT') ?: 3306);

    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
