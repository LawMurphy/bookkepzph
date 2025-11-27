<?php
require_once 'auth_check.php';
require_once 'config.php';

function current_user() {
    if (!empty($_SESSION['user_id'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login");
        exit;
    }
}

function require_role($roles = []) {
    $user = current_user();
    if (!$user || !in_array($user['role'], (array)$roles) || !$user['is_active']) {
        header("HTTP/1.1 403 Forbidden");
        echo "403 Forbidden — You don't have permission.";
        exit;
    }
}
