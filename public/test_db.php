<?php
require_once __DIR__ . '/../app/model/db.php';

$conn = conn();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connection successful!";
}
?>