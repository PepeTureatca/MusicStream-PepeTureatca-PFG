<?php
require_once __DIR__ . '/../app/controller/UserController.php';

$action = $_GET['action'] ?? 'show';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($action) {
        'update'         => UserController::update(),
        'updatePassword' => UserController::updatePassword(),
        default          => UserController::show(),
    };
} else {
    UserController::show();
}