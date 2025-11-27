<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/header.php';
require_once '../auth_check.php';

function generateBusinessID($length = 8) {
    return strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $business_id = generateBusinessID();
    $business_name = $_POST['business_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($business_name != '') {
        $stmt = $pdo->prepare("INSERT INTO customers (business_id, business_name, email, phone, address) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$business_id, $business_name, $email, $phone, $address]);

        $success = "Customer added successfully with BusinessID: $business_id";
    } else {
        $error = "Business Name is required!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Customer</title>
</head>
<style>
:root {
  --primary: #8B5E3C;        /* navbar color - keep */
  --accent: #CBB499;
  --background: #CBB499;     /* page background */
  --card-bg: #FFFFFF;
  --text: #3E2E20;
  --border: #E7DACC;
}

body {
    background: var(--background);
    font-family: "Inter", sans-serif;
    margin: 0;
    padding: 0;
    color: var(--text);
}

.main-content {
    background: #CBB499;
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

/* Container */
.customer-wrapper {
    max-width: 900px;
    margin: 40px auto;
    background: var(--card-bg);
    padding: 40px 50px;
    border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
}

/* Title */
.customer-wrapper h2 {
    margin-top: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: .5px;
    margin-bottom: 25px;
}

/* Form */
.customer-form label {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 6px;
    display: block;
    color: var(--text);
}

.customer-form input,
.customer-form textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: #fdfbf8;
    font-size: 15px;
    transition: 0.2s ease;
    color: var(--text);
}

.customer-form input:focus,
.customer-form textarea:focus {
    border-color: var(--primary);
    outline: none;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.15);
}

.customer-form textarea {
    min-height: 100px;
    resize: vertical;
}

/* Button */
.customer-form button {
    background: var(--primary);
    color: white;
    border: none;
    padding: 14px 22px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.2s ease;
    width: 100%;
    margin-top: 10px;
}

.customer-form button:hover {
    background: #734b2e;
    transform: translateY(-2px);
}

/* Success / Error */
.alert-success, .alert-error {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 15px;
}

.alert-success {
    background: #d9f4e3;
    border: 1px solid #a5e2bd;
    color: #1b6b33;
}

.alert-error {
    background: #ffe2e2;
    border: 1px solid #ffb7b7;
    color: #a83232;
}

/* Fade out after 5 seconds */
.fade-out {
    animation: fadeOut 5s forwards;
}

@keyframes fadeOut {
    0% { opacity: 1; }
    80% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-10px); }
}
</style>

<body>
<div class="customer-wrapper">
    <h2>Add New Customer</h2>

    <?php if(!empty($success)) echo "<div class='alert-success fade-out'>$success</div>"; ?>
    <?php if(!empty($error)) echo "<div class='alert-error fade-out'>$error</div>"; ?>

    <form method="post" action="" class="customer-form">

        <label>Business Name</label>
        <input type="text" name="business_name" required>

        <br><br>

        <label>Email Address</label>
        <input type="email" name="email">

        <br><br>

        <label>Phone Number</label>
        <input type="text" name="phone">

        <br><br>

        <label>Address</label>
        <textarea name="address"></textarea>

        <br><br>

        <button type="submit">Add Customer</button>
    </form>
</div>
<script>
// Prevent form resubmission after refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Auto-clear form after success
<?php if (!empty($success)) : ?>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelector(".customer-form").reset();
});
<?php endif; ?>
</script>

</body>
</html>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>