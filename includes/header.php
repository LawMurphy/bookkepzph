<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ../login');
    exit;
}

date_default_timezone_set('Asia/Manila');

// Base path resolver
$currentPath = $_SERVER['PHP_SELF'];
if (strpos($currentPath, '/admin/') !== false) {
    $basePath = '../';
} elseif (strpos($currentPath, '/staff/group1/') !== false || strpos($currentPath, '/staff/group2/') !== false) {
    $basePath = '../../';
} else {
    $basePath = '../';
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$staff_group = $_SESSION['staff_group'] ?? '';

// Fetch user info
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$first = $user['first_name'] ?? '';
$last = $user['last_name'] ?? '';
$initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

// Fetch permissions if staff
$permissions = [
    'can_view_reports' => 0,
    'can_manage_payroll' => 0,
    'can_manage_sales' => 0,
    'can_manage_inventory' => 0
];

if ($role === 'staff') {
    $perm_stmt = $pdo->prepare("SELECT can_view_reports, can_manage_payroll, can_manage_sales, can_manage_inventory 
                                FROM user_permissions WHERE user_id = ?");
    $perm_stmt->execute([$userId]);
    $userPerms = $perm_stmt->fetch(PDO::FETCH_ASSOC);

    if ($userPerms) {
        foreach ($permissions as $key => $val) {
            $permissions[$key] = (int)$userPerms[$key];
        }
    }
}

// Page title
$pageTitle = "Bookkepz";
if ($role === 'admin') {
    $pageTitle = "Admin | Bookkepz";
} elseif ($role === 'staff') {
    $pageTitle = ($staff_group === 'group1') ? "Dashboard | Bookkepz" : "Dashboard | Bookkepz";
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= $basePath ?>assets/css/admin.css">
  <link rel="stylesheet" href="<?= $basePath ?>assets/css/navbar.css">
  <link rel="icon" type="image/png" href="<?= $basePath ?>assets/img/bookkepz_logo.png">
</head>
<body>

<header class="topbar">
  <div class="topbar-left">
    <a href="<?= $basePath ?><?= ($role === 'admin') ? 'admin/dashboard' : (($staff_group === 'group1') ? 'staff/dashboard' : 'staff/dashboard') ?>" class="logo-link">
      <img src="<?= $basePath ?>assets/img/bookkepz_logo.png" class="logo" alt="Bookkepz Logo">
    </a>
    <h2 class="brand-name">Bookkepz</h2>
  </div>

  <nav class="nav-links">
    <ul>
      <li><a href="<?= $basePath ?><?= ($role === 'admin') ? 'admin/dashboard' : (($staff_group === 'group1') ? 'staff/dashboard' : 'staff/dashboard') ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>

  <?php if ($role === 'admin'): ?>
    <!-- ===== ADMIN MENUS ===== -->
    <li class="has-dropdown">
      <a href="#">Sales</a>
      <ul class="dropdown-menu">
        <li><a href="<?= $basePath ?>customer/customer">Customer</a></li>
        <li><a href="<?= $basePath ?>sales/active-invoices">Transactions</a></li>
      </ul>
    </li>

    <li class="has-dropdown">
      <a href="#">Reports</a>
      <ul class="dropdown-menu">
        <li><a href="<?= $basePath ?>admin/reports">Monthly Reports</a></li>
        <li><a href="<?= $basePath ?>admin/financial_summary">Financial Summary</a></li>
        <li><a href="<?= $basePath ?>admin/audit">Audit Logs</a></li>
      </ul>
    </li>

    <li class="has-dropdown">
      <a href="#">Payroll</a>
      <ul class="dropdown-menu">
        <li><a href="<?= $basePath ?>admin/payroll">Employee Payroll</a></li>
        <li><a href="<?= $basePath ?>admin/benefits">Benefits</a></li>
        <li><a href="<?= $basePath ?>admin/deductions">Deductions</a></li>
      </ul>
    </li>

  <?php elseif ($role === 'staff'): ?>
    <!-- ===== STAFF MENUS (permission-based) ===== -->

    <?php if ($permissions['can_manage_sales']): ?>
      <li class="has-dropdown">
        <a href="#">Sales</a>
        <ul class="dropdown-menu">
          <li><a href="<?= $basePath ?>customer/customer">Customer</a></li>
          <li><a href="<?= $basePath ?>sales/active-invoices">Transactions</a></li>
        </ul>
      </li>
    <?php endif; ?>

    <?php if ($permissions['can_view_reports']): ?>
      <li class="has-dropdown">
        <a href="#">Reports</a>
        <ul class="dropdown-menu">
          <li><a href="<?= $basePath ?>staff/reports">Generate Reports</a></li>
          <li><a href="<?= $basePath ?>staff/report_history">Report History</a></li>
        </ul>
      </li>
    <?php endif; ?>

    <?php if ($permissions['can_manage_payroll']): ?>
      <li class="has-dropdown">
        <a href="#">Payroll</a>
        <ul class="dropdown-menu">
          <li><a href="<?= $basePath ?>staff/payroll_records">Payroll Records</a></li>
          <li><a href="<?= $basePath ?>staff/benefits">Benefits</a></li>
          <li><a href="<?= $basePath ?>staff/deductions">Deductions</a></li>
        </ul>
      </li>
    <?php endif; ?>

    <?php if ($permissions['can_manage_inventory']): ?>
      <li><a href="<?= $basePath ?>staff/inventory">Inventory</a></li>
    <?php endif; ?>

  <?php endif; ?>
    </ul>
  </nav>

  <div class="topbar-right">
    <div class="datetime">
      <span id="current-date"></span>
      <span id="current-time"></span>
    </div>

    <div class="profile-wrapper">
      <div class="profile-circle"><?= htmlspecialchars($initials) ?></div>
      <div class="profile-dropdown">
        <a href="<?= $basePath ?>includes/settings?tab=profile">Profile</a>
        <a href="<?= $basePath ?>includes/settings">Settings</a>
        <a href="<?= $basePath ?>logout">Logout</a>
      </div>
    </div>
  </div>
</header>

<script>
function updateDate() {
  const now = new Date();
  document.getElementById("current-date").textContent =
    now.toLocaleDateString("en-US", {
      weekday: "long",
      month: "long",
      day: "numeric",
      year: "numeric"
    });
}
function updateTime() {
  const now = new Date();
  document.getElementById("current-time").textContent =
    now.toLocaleTimeString("en-US", { hour12: true });
}
updateDate();
updateTime();
setInterval(updateTime, 1000);
</script>

<main class="main-content">
