  <?php
  if (session_status() === PHP_SESSION_NONE) session_start();
  require_once '../auth_check.php';
  require_once '../config.php';
  require_once '../email.php';

  date_default_timezone_set('Asia/Manila');

  // Restrict access to admin only
  if ($_SESSION['role'] !== 'admin') {
      header("Location: ../index.php");
      exit;
  }

  $message = "";

  // ✅ Handle Invite Submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite_user'])) {
      $first_name = trim($_POST['first_name']);
      $last_name  = trim($_POST['last_name']);
      $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

      if (!$first_name || !$last_name || !$email) {
          $_SESSION['msg'] = "<div class='error'>All fields are required.</div>";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $_SESSION['msg'] = "<div class='error'>Invalid email format.</div>";
      } else {
          // Check if email already exists
          $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
          $check->execute([$email]);

          if ($check->rowCount() > 0) {
              $_SESSION['msg'] = "<div class='error'>This email is already registered.</div>";
          } else {
              // Generate invite token
              $token = bin2hex(random_bytes(16));
              $expires = date('Y-m-d H:i:s', time() + 86400); // 24 hours

              // Get admin business name
              $admin_id = $_SESSION['user_id'];
              $admin = $pdo->prepare("SELECT business_name FROM users WHERE id = ?");
              $admin->execute([$admin_id]);
              $admin_business = $admin->fetchColumn();

              // Insert staff record
              $insert = $pdo->prepare("
                  INSERT INTO users (first_name, last_name, email, business_name, role, is_active, invite_token, invite_expires)
                  VALUES (?, ?, ?, ?, 'staff', 0, ?, ?)
              ");
              $insert->execute([$first_name, $last_name, $email, $admin_business, $token, $expires]);

              // Send invitation email
              $link = "http://localhost/bookkepz/setup_password.php?token=$token";
              $result = sendInvitationEmail($email, "$first_name $last_name", $link);

              if ($result === true) {
                  $_SESSION['msg'] = "<div class='success'>Invitation sent successfully to $email</div>";
              } else {
                  $_SESSION['msg'] = "<div class='error'>Failed to send email: $result</div>";
              }
          }
      }

      header("Location: settings?tab=users");
      exit;
  }

  // ✅ Show message if set
  if (isset($_SESSION['msg'])) {
      $message = $_SESSION['msg'];
      unset($_SESSION['msg']);
  }

  // ✅ Fetch all staff for this business
  $admin_id = $_SESSION['user_id'];
  $admin = $pdo->prepare("SELECT business_name FROM users WHERE id = ?");
  $admin->execute([$admin_id]);
  $business_name = $admin->fetchColumn();

  $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, is_active FROM users WHERE business_name = ? AND role = 'staff'");
  $stmt->execute([$business_name]);
  $staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <?php if (!empty($message)): ?>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const msgHTML = <?= json_encode(strip_tags($message)) ?>;
    let color = '#28a745';
    let icon = '✅';
    if (msgHTML.toLowerCase().includes('error') || msgHTML.toLowerCase().includes('fail') || msgHTML.toLowerCase().includes('already')) {
      color = '#dc3545';
      icon = '❌';
    }

    const notify = document.createElement('div');
    notify.textContent = `${icon} ${msgHTML}`;
    Object.assign(notify.style, {
      position: 'fixed',
      top: '80px',
      right: '20px',
      background: color,
      color: '#fff',
      padding: '14px 22px',
      borderRadius: '10px',
      fontSize: '14px',
      fontWeight: '500',
      zIndex: '9999',
      boxShadow: '0 4px 10px rgba(0,0,0,0.25)',
      opacity: '0',
      transform: 'translateY(-10px)',
      transition: 'opacity 0.4s ease, transform 0.4s ease'
    });

    document.body.appendChild(notify);

    setTimeout(() => {
      notify.style.opacity = '1';
      notify.style.transform = 'translateY(0)';
    }, 100);

    setTimeout(() => {
      notify.style.opacity = '0';
      notify.style.transform = 'translateY(-10px)';
      setTimeout(() => notify.remove(), 400);
    }, 3500);
  });
  </script>
  <?php endif; ?>


  <div class="settings-container">
    <h2>Manage Users</h2>

    <form method="POST" class="settings-form">
      <h3>Invite New Staff</h3>

      <label>First Name</label>
      <input type="text" name="first_name" required>

      <label>Last Name</label>
      <input type="text" name="last_name" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <button type="submit" name="invite_user">Send Invitation</button>
    </form>

    <h3 style="margin-top:40px;">Staff List</h3>
    <table class="users-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Role</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($staff_list) === 0): ?>
          <tr><td colspan="5">No staff added yet.</td></tr>
        <?php else: ?>
          <?php foreach ($staff_list as $staff): ?>
            <tr>
              <td><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
              <td><?= htmlspecialchars($staff['email']) ?></td>
              <td>
                <?= $staff['is_active'] ? "<span style='color:green;'>Active</span>" : "<span style='color:orange;'>Pending Setup</span>" ?>
              </td>
              <td><?= ucfirst($staff['role']) ?></td>
              <td>
                <form method="GET" action="manage_permissions" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $staff['id'] ?>">
                  <button type="submit" class="btn-small">Edit Permissions</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

