<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

/**
 * Universal mail sending function
 */
function sendMail($to, $subject, $body, $name = '') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bookkepzofficial@gmail.com';
        $mail->Password   = 'byrs tfuw fgmc fixv'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('bookkepzofficial@gmail.com', 'Bookkepz (No-Reply)');
        $mail->addReplyTo('no-reply@bookkepz.com', 'No Reply');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send(); // true if successful
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

/**
 * Send verification code email (for registration)
 */
function sendVerificationEmail($to, $name, $code) {
    if (!$to || !$code) return 'Missing required parameters.';

    $subject = 'Bookkepz Verification Code';
    $message = "
        <p>Hello $name,</p>
        <p>Your verification code is: <b>$code</b></p>
        <p>Thank you for registering with Bookkepz!</p>
    ";

    return sendMail($to, $subject, $message, $name);
}

/**
 * Send staff invitation email
 */
function sendInvitationEmail($email, $name, $link) {
    $subject = "You're Invited to Join Bookkepz";
    $body = "
        <h2>Hello $name,</h2>
        <p>You’ve been invited to join Bookkepz as a staff member.</p>
        <p>Please click the link below to set up your password and activate your account:</p>
        <p><a href='$link' style='color:#8B5E3C; font-weight:bold;'>Set Up Your Account</a></p>
        <br><p><small>This link will expire in 24 hours.</small></p>
    ";

    return sendMail($email, $subject, $body, $name);
}
?>
