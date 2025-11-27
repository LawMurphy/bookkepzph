<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../auth_check.php';
require_once '../config.php';

// Ensure only admin
if ($_SESSION['role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

// Get user_id from GET or POST
$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
if (!$user_id) die("Invalid user ID.");

// Fetch user info
$user_stmt = $pdo->prepare("SELECT first_name, last_name, email, is_active, address, phone, profile_image FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// Fetch or create permissions
$perm_stmt = $pdo->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
$perm_stmt->execute([$user_id]);
$permissions = $perm_stmt->fetch(PDO::FETCH_ASSOC);

if (!$permissions) {
  $pdo->prepare("
    INSERT INTO user_permissions (user_id, email, can_view_reports, can_manage_payroll, can_manage_sales, can_manage_inventory)
    VALUES (?, ?, 0, 0, 0, 0)
  ")->execute([$user_id, $user['email']]);
  $perm_stmt->execute([$user_id]);
  $permissions = $perm_stmt->fetch(PDO::FETCH_ASSOC);
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
  $fields = [
    'can_view_reports' => isset($_POST['can_view_reports']) ? 1 : 0,
    'can_manage_payroll' => isset($_POST['can_manage_payroll']) ? 1 : 0,
    'can_manage_sales' => isset($_POST['can_manage_sales']) ? 1 : 0,
    'can_manage_inventory' => isset($_POST['can_manage_inventory']) ? 1 : 0,
  ];

  $update = $pdo->prepare("
    UPDATE user_permissions 
    SET email = :email,
        can_view_reports = :r, 
        can_manage_payroll = :p, 
        can_manage_sales = :i, 
        can_manage_inventory = :inv
    WHERE user_id = :uid
  ");
  $update->execute([
    ':email' => $user['email'],
    ':r' => $fields['can_view_reports'],
    ':p' => $fields['can_manage_payroll'],
    ':i' => $fields['can_manage_sales'],
    ':inv' => $fields['can_manage_inventory'],
    ':uid' => $user_id
  ]);

  $new_status = (int) $_POST['is_active'];
  $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new_status, $user_id]);

  $_SESSION['msg'] = "Permissions and status updated successfully!";
  header("Location: manage_permissions.php?user_id=" . urlencode($user_id) . "&saved=1");
  exit;
}

// ✅ Create initials if no image
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
if ($user['profile_image']) {
    if (str_starts_with($user['profile_image'], 'http')) {
        // Google Cloud or any external URL
        $profile_image = $user['profile_image'];
    } else {
        // Local upload inside your project
        $profile_image = "../" . $user['profile_image'];
    }
} else {
    $profile_image = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Permissions | Bookkepz</title>
  <link rel="stylesheet" href="../assets/css/settings.css">

  <style>
  /* ✅ Floating Notification */
  .notification {
    position: fixed;
    top: 25px;
    right: 25px;
    background: #28a745;
    color: #fff;
    padding: 14px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    opacity: 0;
    transform: translateX(100%);
    animation: slideInOut 4s ease forwards;
    z-index: 9999;
  }
  .notification.error {
    background: #dc3545;
  }
  .notification::before {
    content: "✔";
    font-weight: bold;
  }
  .notification.error::before {
    content: "✖";
  }
  @keyframes slideInOut {
    0% { opacity: 0; transform: translateX(100%); }
    10%, 90% { opacity: 1; transform: translateX(0); }
    100% { opacity: 0; transform: translateX(100%); }
  }

  /* ✅ Profile initials circle */
  .profile-circle1 {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: #a97a5e;
    color: white;
    font-size: 28px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    margin: 10px auto 20px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  }
  .profile-circle1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* ✅ Modal view */
  .modal {
    display: none;
    position: fixed;
    z-index: 9998;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
  }
  .modal-content img {
    max-width: 300px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }
  </style>
</head>
<body>

<div class="settings-container">
  <h2>EDIT PERMISSION</h2>

  <!-- ✅ Floating Notification -->
  <?php if (!empty($_SESSION['msg'])): ?>
    <?php
      $msg = strip_tags($_SESSION['msg']);
      $is_error = stripos($msg, 'error') !== false || stripos($msg, 'fail') !== false;
    ?>
    <div class="notification <?= $is_error ? 'error' : '' ?>">
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php unset($_SESSION['msg']); ?>
  <?php endif; ?>

  <form method="POST" class="permissions-form">
    <!-- ✅ Profile Display -->
    <div class="profile-pic-box">
      <div class="profile-circle1" id="profileAvatar">
        <?php if ($profile_image): ?>
          <img src="<?= $profile_image ?>" alt="Profile Image">
        <?php else: ?>
          <?= htmlspecialchars($initials) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="info-grid">
      <div><strong>NAME:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
      <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
      <div><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></div>
      <div><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></div>

      <div class="status-section">
        <label for="is_active"><strong>Status:</strong></label>
        <select name="is_active" id="is_active">
          <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="permissions-box">
      <label><input type="checkbox" name="can_manage_sales" <?= $permissions['can_manage_sales'] ? 'checked' : '' ?>> Manage Business</label>
      <label><input type="checkbox" name="can_manage_inventory" <?= $permissions['can_manage_inventory'] ? 'checked' : '' ?>> Manage Inventory</label>
      <label><input type="checkbox" name="can_manage_payroll" <?= $permissions['can_manage_payroll'] ? 'checked' : '' ?>> Manage Payroll</label>
      <label><input type="checkbox" name="can_view_reports" <?= $permissions['can_view_reports'] ? 'checked' : '' ?>> View Reports</label>
    </div>

    <div class="button-group">
      <button type="submit" name="save_permissions" class="btn-primary">Save Changes</button>
      <a href="settings.php?tab=users" class="btn-secondary">Back</a>
    </div>

    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
  </form>
</div>

<!-- ✅ Modal for viewing profile -->
<?php if ($profile_image): ?>
<div id="profileModal" class="modal">
  <div class="modal-content">
    <img src="<?= $profile_image ?>" alt="Full Profile">
  </div>
</div>
<?php endif; ?>

<script>
  const avatar = document.getElementById('profileAvatar');
  const modal = document.getElementById('profileModal');

  if (avatar && modal) {
    avatar.addEventListener('click', () => modal.style.display = 'flex');
    modal.addEventListener('click', () => modal.style.display = 'none');
  }

  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

</body>
</html>
