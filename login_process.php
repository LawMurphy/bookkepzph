<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: login?error=Please fill in all fields.");
        exit;
    }

    try {
        // Fetch user info
        $stmt = $pdo->prepare("SELECT id, first_name, email, password, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: login?error=Account not found.");
            exit;
        }

        // 🔒 Check if account is active
        if ((int)$user['is_active'] === 0) {
            header("Location: login?error=Your account is inactive. Please contact admin to activate it.");
            exit;
        }

        // 🔑 Verify password
        if (!password_verify($password, $user['password'])) {
            header("Location: login?error=Invalid email or password.");
            exit;
        }

        // ✅ Store session info
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        // ✅ Fetch permissions if staff
        if ($user['role'] === 'staff') {
            $permStmt = $pdo->prepare("
                SELECT can_view_reports, can_manage_payroll, can_manage_sales, can_manage_inventory
                FROM user_permissions
                WHERE user_id = ?
            ");
            $permStmt->execute([$user['id']]);
            $_SESSION['permissions'] = $permStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        // ✅ Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard");
        } else {
            header("Location: staff/dashboard");
        }
        exit;

    } catch (Exception $e) {
        header("Location: login?error=Server error: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: login");
    exit;
}
?>
