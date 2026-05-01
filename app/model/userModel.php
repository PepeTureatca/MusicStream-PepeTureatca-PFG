<?php
require_once __DIR__ . '/db.php';

class User
{

    private static function existeColumnaRole($conn)
    {
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
        return $result && $result->num_rows > 0;
    }

    private static function asegurarColumnaRole($conn)
    {
        if (self::existeColumnaRole($conn)) {
            return true;
        }

        // Añadimos rol con valor por defecto 'user' para no romper usuarios existentes.
        return (bool) $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER password");
    }

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

    public static function crearAdmin($name, $email, $password)
    {
        $conn = conn();

        if (!self::asegurarColumnaRole($conn)) {
            return false;
        }

        $hasheada = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $name, $email, $hasheada);

        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        return false;
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
        $stmt = $conn->prepare("SELECT id, name, email, google_id, is_premium FROM users WHERE id = ?");
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

    public static function setPremium(int $userId, string $stripePaymentId): bool
    {
        $conn = conn();
        $now  = date('Y-m-d H:i:s');
        $stmt = $conn->prepare(
            "UPDATE users
             SET is_premium = 1,
                 premium_since = ?,
                 stripe_payment_id = ?,
                 subscription_status = 'active'
             WHERE id = ?"
        );
        $stmt->bind_param("ssi", $now, $stripePaymentId, $userId);
        return $stmt->execute();
    }

    public static function esPremium(int $userId): bool
    {
        $conn = conn();
        $stmt = $conn->prepare("SELECT is_premium FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (bool) ($row['is_premium'] ?? false);
    }
}