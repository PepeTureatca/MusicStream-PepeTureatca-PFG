<?php
function conn()
{
    $env = 'local'; // cambiar a 'prod' cuando subo a Ionos

    if ($env === 'local') {
        $host = '127.0.0.1';
        $user = 'dios';
        $pass = '1234';
        $db   = 'mi_spotify_tfg';
        $port = 3306;
    } else {
        $host = 'db5019710463.hosting-data.io';
        $port = 3306;
        $user = 'dbu527652';
        $pass = 'passwdDB-MusicStream-admin';
        $db   = 'dbs15320198';
    }

    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
