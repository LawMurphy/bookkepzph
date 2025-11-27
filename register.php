<?php
session_start();
require_once 'config.php';
require_once 'email.php'; // use your mailer file

$message = "";

// show flash messages
if (isset($_SESSION['flash'])) {
  $message = $_SESSION['flash'];
  unset($_SESSION['flash']);
}

// STEP 1: send verification code
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['step'] ?? '') === "1") {
  $first_name = htmlspecialchars(trim($_POST['first_name']));
  $last_name  = htmlspecialchars(trim($_POST['last_name']));
  $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

  if (!$first_name || !$last_name || !$email) {
    $_SESSION['flash'] = "<div class='error message'>All fields are required.</div>";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = "<div class='error message'>Invalid email format!</div>";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $_SESSION['flash'] = "<div class='error message'>Email already registered!</div>";
    } else {
      $code = rand(100000, 999999);

      $_SESSION['register_temp'] = [
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'email'      => $email,
        'code'       => $code,
        'code_time'  => time()
      ];

      $result = sendVerificationEmail($email, "$first_name $last_name", $code);
      if ($result === true) {
        $_SESSION['flash'] = "<div class='success message'>Verification code sent to $email</div>";
        header("Location: register.php?step=2");
        exit;
      } else {
        $_SESSION['flash'] = "<div class='error message'>Failed to send email: $result</div>";
      }
    }
  }
  header("Location: register.php");
  exit;
}

// STEP 2: resend code
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend'])) {
  if (isset($_SESSION['register_temp'])) {
    $temp = $_SESSION['register_temp'];
    $code = rand(100000, 999999);
    $_SESSION['register_temp']['code'] = $code;
    $_SESSION['register_temp']['code_time'] = time();

    $result = sendVerificationEmail($temp['email'], "{$temp['first_name']} {$temp['last_name']}", $code);
    if ($result === true) {
      $_SESSION['flash'] = "<div class='success message'>New code sent to {$temp['email']}</div>";
    } else {
      $_SESSION['flash'] = "<div class='error message'>Failed to resend code: $result</div>";
    }
  } else {
    $_SESSION['flash'] = "<div class='error message'>Session expired. Please start again.</div>";
  }
  header("Location: register.php?step=2");
  exit;
}

// STEP 2: verify code
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['step'] ?? '') === "2") {
  if (!isset($_SESSION['register_temp'])) {
    $_SESSION['flash'] = "<div class='error message'>Session expired. Please start again.</div>";
    header("Location: register.php");
    exit;
  }

  $temp = $_SESSION['register_temp'];
  $input_code = trim($_POST['verification_code'] ?? '');

  if (time() - $temp['code_time'] > 300) {
    unset($_SESSION['register_temp']);
    $_SESSION['flash'] = "<div class='error message'>Verification code expired. Please start again.</div>";
    header("Location: register.php");
    exit;
  } elseif ($input_code != $temp['code']) {
    $_SESSION['flash'] = "<div class='error message'>Invalid verification code!</div>";
    header("Location: register.php?step=2");
    exit;
  } else {
    $_SESSION['flash'] = "<div class='success message'>Code verified! Complete your registration below.</div>";
    header("Location: register.php?step=3");
    exit;
  }
}

// STEP 3: final registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['step'] ?? '') === "3") {
  if (!isset($_SESSION['register_temp'])) {
    $_SESSION['flash'] = "<div class='error message'>Session expired. Please start again.</div>";
    header("Location: register.php");
    exit;
  }

  $temp = $_SESSION['register_temp'];
  $business_name = htmlspecialchars(trim($_POST['business_name']));
  $address       = htmlspecialchars(trim($_POST['address']));
  $phone         = trim($_POST['phone']);
  $password      = $_POST['password'] ?? '';
  $confirm       = $_POST['confirm'] ?? '';

  if (!$business_name || !$address || !$phone || !$password || !$confirm) {
    $_SESSION['flash'] = "<div class='error message'>All fields are required.</div>";
    header("Location: register.php?step=3");
    exit;
  } elseif (!preg_match('/^[0-9]{7,15}$/', $phone)) {
    $_SESSION['flash'] = "<div class='error message'>Phone number must be numbers only (7-15 digits).</div>";
    header("Location: register.php?step=3");
    exit;
  } elseif ($password !== $confirm) {
    $_SESSION['flash'] = "<div class='error message'>Passwords do not match!</div>";
    header("Location: register.php?step=3");
    exit;
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("
      INSERT INTO users 
      (first_name, last_name, email, business_name, address, phone, password, role)
      VALUES (?, ?, ?, ?, ?, ?, ?, 'admin')
    ");
    $insert->execute([
      $temp['first_name'], $temp['last_name'], $temp['email'],
      $business_name, $address, $phone, $hashed
    ]);

    unset($_SESSION['register_temp']);
    $_SESSION['flash'] = "<div class='success message'>Registration complete!</div>";
    header("Location: register.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Bookkepz</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
  .message {
    transition: opacity 0.8s ease;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 6px;
    font-weight: 500;
  }
  .success { background: #c3f7c0; color: #0b4d0b; border: 1px solid #8be28b; }
  .error { background: #ffd2d2; color: #8a1a1a; border: 1px solid #ff9c9c; }
  .hidden { opacity:0; transition:0.3s; }
  .timer { font-size:0.9em; margin:5px 0; color:#fff; }
</style>
</head>
<body>
<div class="container">
  <a href="index.php"><img src="assets/img/bookkepz_logo.png" class="logo" alt="Bookkepz Logo"></a>
  <h2>Register Account</h2>
  <?= $message ?>

  <!-- Step 1 -->
  <form method="POST" class="step1" style="display:none;">
    <input type="hidden" name="step" value="1">
    <label>First Name</label>
    <input type="text" name="first_name" required>
    <label>Last Name</label>
    <input type="text" name="last_name" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <button type="submit">Next</button>
  </form>

  <!-- Step 2 -->
  <form method="POST" class="step2" style="display:none;">
    <input type="hidden" name="step" value="2">
    <label>Verification Code</label>
    <input type="text" name="verification_code" required>
    <div class="timer" id="countdown"></div>
    <button type="submit">Verify Code</button>
  </form>

  <form method="POST" id="resendForm" class="step2" style="display:none;margin-top:5px;">
    <input type="hidden" name="resend" value="1">
    <button type="submit">Resend Code</button>
  </form>

  <!-- Step 3 -->
  <form method="POST" class="step3" style="display:none;">
      <input type="hidden" name="step" value="3">
      <label>Business Name</label>
      <input type="text" name="business_name" required>
      <label>Address</label>
      <input type="text" name="address" required>
      <label>Phone Number</label>
      <input type="text" name="phone" pattern="[0-9]{7,15}" placeholder="Numbers only" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <label>Confirm Password</label>
      <input type="password" name="confirm" required>
      <button type="submit">Complete Registration</button>
  </form>

  <p>Already have an account? <a href="login">Login</a></p>
</div>

<script>
let timerInterval;

function showStep2() {
  document.querySelector('.step1').style.display = 'none';
  document.querySelectorAll('.step2').forEach(f => f.style.display = 'flex');
  startTimer();
}

function showStep3() {
  document.querySelectorAll('.step1, .step2').forEach(f => f.style.display = 'none');
  document.querySelector('.step3').style.display = 'flex';
}

function resetForm() {
  document.querySelectorAll('.step2, .step3').forEach(f => f.style.display = 'none');
  document.querySelector('.step1').style.display = 'flex';
}

// countdown timer
function startTimer() {
  let timeLeft = 300;
  const countdownEl = document.getElementById('countdown');
  const resendBtn = document.querySelector('#resendForm button');

  resendBtn.disabled = true;
  resendBtn.style.opacity = 0.5;

  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    countdownEl.textContent = `Code expires in ${minutes}:${seconds < 10 ? '0'+seconds : seconds}`;
    if (--timeLeft < 0) {
      clearInterval(timerInterval);
      countdownEl.textContent = "Code expired. You can resend the code now.";
      resendBtn.disabled = false;
      resendBtn.style.opacity = 1;
    }
  }, 1000);
}

// detect step from URL
window.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const step = params.get("step");

  if (step === "2") showStep2();
  else if (step === "3") showStep3();
  else document.querySelector('.step1').style.display = 'flex';

  // fade out messages
  const msg = document.querySelector('.message');
  if (msg) {
    setTimeout(() => {
      msg.style.opacity = '0';
      setTimeout(() => msg.remove(), 800);
    }, 4000);
  }
});
</script>
</body>
</html>
