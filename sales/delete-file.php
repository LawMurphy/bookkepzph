<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

// Get invoice ID and file URL path
$invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;
$file_path  = isset($_GET['file']) ? $_GET['file'] : '';

if (!$invoice_id || !$file_path) {
    $_SESSION['msg'] = "❌ Missing invoice ID or file.";
    header("Location: active-invoices?id={$invoice_id}");
    exit;
}

// Fetch current attachments
$stmt = $pdo->prepare("SELECT attachments FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$attachments_json = $stmt->fetchColumn();
$attachments = json_decode($attachments_json, true) ?: [];

$found = false;

// Remove the file if it exists
foreach ($attachments as $key => $attUrl) {
    $attUrlClean = str_replace('\\/', '/', $attUrl);
    if (strpos($attUrlClean, $file_path) !== false) {
        unset($attachments[$key]);
        $found = true;
        break;
    }
}

if ($found) {
    $attachments = array_values($attachments);
    $stmt = $pdo->prepare("UPDATE invoices SET attachments = ? WHERE id = ?");
    $stmt->execute([json_encode($attachments), $invoice_id]);
    $_SESSION['msg'] = "✅ Attachment removed from invoice.";
} else {
    $_SESSION['msg'] = "⚠️ File not found in invoice attachments.";
}

header("Location: active-invoices?id={$invoice_id}");
exit;
?>
