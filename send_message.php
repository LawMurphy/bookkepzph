<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (empty($name) || empty($email) || empty($message)) {
        header("Location: landingpage?error=Please+fill+in+all+fields");
        exit;
    }

    $subject = "New message from Bookkepz Contact Form";
    $body = "
        <h3>You received a new message from the Bookkepz website</h3>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Message:</strong></p>
        <p>{$message}</p>
    ";

    // Prepare data for email.php
    $_POST['email']   = "bookkepzofficial@gmail.com"; // Bookkepz email RECEIVES the message
    $_POST['subject'] = $subject;
    $_POST['message'] = $body;
    $_POST['replyto'] = $email; // so Bookkepz can reply directly to the sender

    include "email.php"; // send the email via PHPMailer

    header("Location: landingpage?success=Message+sent+successfully");
    exit;
} else {
    header("Location: landingpage");
    exit;
}
?>
