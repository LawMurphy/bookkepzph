<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../auth_check.php';
require_once '../config.php';

if ($_SESSION['role'] !== 'admin') {
  header("Location: ../index.php");
  exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
  header("Location: manage_users.php");
  exit;
}

// Fetch user
$user = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$user->execute([$user_id]);
$userData = $user->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
  echo "User not found.";
  exit;
}

// Fetch or create permission record
$perm = $pdo->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
$perm->execute([$user_id]);
$permissions = $perm->fetch(PDO::FETCH_ASSOC);

if (!$permissions) {
  $pdo->prepare("INSERT INTO user_permissions (user_id) VALUES (?)")->execute([$user_id]);
  $perm->execute([$user_id]);
  $permissions = $perm->fetch(PDO::FETCH_ASSOC);
}

// Save updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fields = ['can_reports','can_sales','can_payroll','can_clients','can_settings'];
  $values = [];
  foreach ($fields as $f) {
    $values[$f] = isset($_POST[$f]) ? 1 : 0;
  }

  $update = $pdo->prepare("
    UPDATE user_permissions 
    SET can_reports=?, can_sales=?, can_payroll=?, can_clients=?, can_settings=?
    WHERE user_id=?
  ");
  $update->execute([$values['can_reports'], $values['can_sales'], $values['can_payroll'], $values['can_clients'], $values['can_settings'], $user_id]);

  $_SESSION['msg'] = "<div class='success'>Permissions updated for {$userData['first_name']} {$userData['last_name']}.</div>";
  header("Location: manage_users.php");
  exit;
}
?>

<div class="settings-container">
  <h2>Edit Permissions for <?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></h2>

  <form method="POST" class="settings-form">
    <label><input type="checkbox" name="can_reports" <?= $permissions['can_reports'] ? 'checked' : '' ?>> Reports</label>
    <label><input type="checkbox" name="can_sales" <?= $permissions['can_sales'] ? 'checked' : '' ?>> Invoices</label>
    <label><input type="checkbox" name="can_payroll" <?= $permissions['can_payroll'] ? 'checked' : '' ?>> Payroll</label>
    <label><input type="checkbox" name="can_clients" <?= $permissions['can_clients'] ? 'checked' : '' ?>> Clients</label>
    <label><input type="checkbox" name="can_settings" <?= $permissions['can_settings'] ? 'checked' : '' ?>> Settings</label>

    <button type="submit">Save Permissions</button>
    <a href="manage_users.php" class="btn-small" style="background:#ccc;">Back</a>
  </form>
</div>
