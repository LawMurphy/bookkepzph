<?php
include 'config.php';
date_default_timezone_set('Asia/Manila');

$message = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($token) || empty($new_password)) {
        header("Location: reset_password.php?status=missing");
        exit;
    }

    try {
        // Check valid and not expired token (20 minutes handled in DB check)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Hash and update password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL, reset_expires = NULL 
                WHERE id = ?
            ");
            $update->execute([$hashed, $user['id']]);

            if ($update->rowCount() > 0) {
                header("Location: reset_password.php?status=success");
                exit;
            } else {
                header("Location: reset_password.php?status=error");
                exit;
            }
        } else {
            header("Location: reset_password.php?status=invalid");
            exit;
        }
    } catch (Exception $e) {
        header("Location: reset_password.php?status=error");
        exit;
    }
}

// Display messages
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $message = "✅ Password updated successfully. <a href='login' class='back-btn'>Go to Login</a>";
            break;
        case 'invalid':
            $message = "❌ Invalid or expired token. Please request a new password reset.";
            break;
        case 'missing':
            $message = "⚠️ Missing token or password.";
            break;
        case 'error':
            $message = "⚠️ Something went wrong while updating your password. Please try again.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | Bookkepz</title>
<link rel="stylesheet" href="assets/css/fpass.css">
<link rel="icon" type="img/png" href="assets/img/bookkepz_logo.png">
</head>
<body>
<div class="reset-container">
  <div class="reset-box">
    <h2>Set New Password</h2>

    <?php if (!empty($message)): ?>
      <div class="message"><?= $message ?></div>
    <?php elseif (!empty($token)): ?>
      <form method="POST" autocomplete="off">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label>New Password</label>
        <input type="password" name="password" placeholder="Enter new password" minlength="6" required>
        <button type="submit">Update Password</button>
      </form>
    <?php else: ?>
      <div class="message error">❌ No token provided.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
