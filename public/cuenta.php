<?php
require_once __DIR__ . '/../app/controller/UserController.php';

$action = $_GET['action'] ?? 'show';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($action) {
        'updateAccount' => UserController::updateAccount(),
        'updatePassword' => UserController::updatePassword(),
        default => UserController::showAccount(),
    };
} else {
    UserController::showAccount();
}