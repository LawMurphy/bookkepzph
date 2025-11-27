<?php
session_start();
require_once '../auth_check.php';
require_once '../config.php';

// 🔒 Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// get role
$role = $_SESSION['role'] ?? 'staff';

// tab switcher
$tab = $_GET['tab'] ?? 'profile';

// limit staff access
if ($role !== 'admin' && $tab === 'users') {
    $tab = 'profile';
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings | Bookkepz</title>
<link rel="stylesheet" href="../assets/css/settings.css">
<style>
</style>
</head>
<body>

<div class="settings-container">
  <h2>Settings</h2>
  <div class="tabs">
    <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">Profile Settings</a>
    <a href="?tab=general" class="<?= $tab === 'general' ? 'active' : '' ?>">General</a>
    <?php if ($role === 'admin'): ?>
      <a href="?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>">Manage Users</a>
    <?php endif; ?>
  </div>

  <div class="tab-content">
    <?php
      switch ($tab) {
        case 'general':
          include 'settings_general.php';
          break;
        case 'users':
          include '../admin/manage_users.php';
          break;
        default:
          include 'settings_profile.php';
          break;
      }
    ?>
  </div>
</div>

</body>
</html>
<?php include 'footer.php'; ?>