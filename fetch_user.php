<?php
require_once 'auth_check.php';
require_once 'config.php';
session_start();

// Optional: only allow admin to fetch user list
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, fullname, email, role, status, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>
