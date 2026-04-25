<?php
require_once __DIR__ . '/db.php';

class User
{

    public static function encontrarPorEmail($email)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function verificarPassword($email, $password)
    {
        $user = self::encontrarPorEmail($email);
        if (!$user) return false;
        return password_verify($password, $user['password']);
    }

    public static function crearUsuario($name, $email, $password)
    {
        $conn = conn();


        $hasheada = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hasheada);

        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            return false;
        }
    }

    public static function crearUsuarioGoogle($name, $email, $google_id)
    {
        $conn = conn();
        $stmt = $conn->prepare("INSERT INTO users (name, email, google_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $google_id);
        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            return false;
        }
    }

    public static function encontrarPorNombre($name)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function obtenerPorId($id)
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT id, name, email, google_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function actualizar($id, $name, $email)
    {
        $conn = conn();
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $id);
        return $stmt->execute();
    }

    public static function actualizarPassword($id, $nuevaPassword)
    {
        $conn = conn();
        $hashed = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        return $stmt->execute();
    }
}
