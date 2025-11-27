<?php
session_start();

require_once 'config.php';

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Bookkepz</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="img/png" href="assets/img/bookkepz_logo.png">
</head>
<body>
  <div class="container">
    <a href="index">
      <img src="assets/img/bookkepz_logo.png" class="logo" alt="Bookkepz Logo">
    </a>

    <h1>Login</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login_process.php">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit">Login</button>
    </form>

    <p>Don’t have an account? <a href="register">Register</a></p>
    <p>Forgot Password? <a href="forgot_password">Forget Password</a></p>
  </div>
</body>
</html>
