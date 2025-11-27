<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['token'])) {
    die('Invalid token.');
}

$token = $_GET['token'];

// ✅ Check token validity
$stmt = $pdo->prepare("SELECT id, email, invite_expires FROM users WHERE invite_token = ? AND is_active = 0");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Invalid or expired token.');
}

$current_time = date('Y-m-d H:i:s');
if ($current_time > $user['invite_expires']) {
    die('Invalid or expired token.');
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    $address  = trim($_POST['address']);
    $phone    = trim($_POST['phone']);

    if (empty($password) || empty($confirm)) {
        $error = 'Please enter and confirm your password.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // ✅ Update user info and activate
        $update = $pdo->prepare("UPDATE users SET password = ?, address = ?, phone = ?, is_active = 1, invite_token = NULL, invite_expires = NULL WHERE id = ?");
        $update->execute([$hashed, $address, $phone, $user['id']]);

        $success = 'Your account has been activated! You may now <a href="login.php">login</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Setup Password | Bookkepz</title>
  <link rel="icon" type="image/png" href="assets/img/bookkepz_logo.png">
  <link rel="stylesheet" href="assets/css/setup_password.css">
</head>
<body>
  <div class="background-logo"></div>

  <div class="setup-container">
    <a href="index.php" class="logo-link">
      <img src="assets/img/bookkepz_logo.png" class="logo" alt="Bookkepz Logo">
    </a>
    <h2>Setup Your Account</h2>

    <?php if ($error): ?>
      <p class="message error"><?= $error ?></p>
    <?php elseif ($success): ?>
      <p class="message success"><?= $success ?></p>
    <?php else: ?>
      <form method="POST">
        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="e.g. 0917xxxxxxx">

        <label>Address</label>
        <input type="text" name="address" placeholder="e.g. Manila, Philippines">

        <button type="submit">Activate Account</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
