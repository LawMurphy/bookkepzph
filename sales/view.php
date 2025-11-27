<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

include_once __DIR__ . '/../includes/header.php';

// Validate ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='error-box'>Invalid Invoice ID</div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Fetch invoice
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invoice) {
    echo "<div class='error-box'>Invoice not found</div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Customer info
$customerName = $invoice['customer_name'] ?? null;
$customerId   = $invoice['business_id'] ?? null;

if ($customerId && !$customerName) {
    $stmt = $pdo->prepare("SELECT business_name FROM customers WHERE business_id = ?");
    $stmt->execute([$customerId]);
    $customerName = $stmt->fetchColumn();
} elseif ($customerName && !$customerId) {
    $stmt = $pdo->prepare("SELECT business_id FROM customers WHERE business_name = ?");
    $stmt->execute([$customerName]);
    $customerId = $stmt->fetchColumn();
}

$customerName = $customerName ?: '-';
$customerId   = $customerId ?: '-';

// -----------------------------
// 🔥 Google Cloud Storage Setup
// -----------------------------
$storage = new StorageClient([
    'keyFilePath' => __DIR__ . '/../credentials/bookkepz-key.json'
]);
$bucketName = 'bookkepzfile';
$bucket = $storage->bucket($bucketName);

// Decode attachments JSON (from DB)
$attachments = json_decode($invoice['attachments'], true) ?? [];

$gcsAttachments = [];

foreach ($attachments as $fileUrl) {
    // Clean up escaped slashes
    $fileUrl = str_replace('\\/', '/', $fileUrl);

    // Use URL directly
    $gcsAttachments[] = [
        'name' => basename(parse_url($fileUrl, PHP_URL_PATH)),
        'url'  => $fileUrl,
        'delete_url' => "delete-file.php?invoice_id=$id&file=" . urlencode(parse_url($fileUrl, PHP_URL_PATH))
    ];
}
?>

<link rel="stylesheet" href="../assets/css/invoice-view.css">

<div class="view-page">
    <div class="view-header">
        <h2>Invoice Details</h2>
        <div class="action-buttons">
            <a href="active-invoices" class="btn">← Back</a>
        </div>
    </div>

    <div class="invoice-box">
        <div class="info-grid">
            <div>
                <label>Customer ID</label>
                <div class="value"><?= htmlspecialchars($customerName) ?></div>
            </div>
            <div>
                <label>Business ID</label>
                <div class="value"><?= htmlspecialchars($customerId) ?></div>
            </div>
            <div>
                <label>Status</label>
                <div class="value badge"><?= htmlspecialchars($invoice['status'] ?? '-') ?></div>
            </div>
            <div>
                <label>Invoice Date</label>
                <div class="value"><?= htmlspecialchars($invoice['invoice_date'] ?? '-') ?></div>
            </div>
            <div>
                <label>Due Date</label>
                <div class="value"><?= htmlspecialchars($invoice['due_date'] ?? '-') ?></div>
            </div>
            <div>
                <label>Invoice Ref</label>
                <div class="value"><?= htmlspecialchars($invoice['invoice_ref'] ?? '-') ?></div>
            </div>
        </div>

        <h3>Financial Summary</h3>
        <table class="details-table">
            <tr><td>Sale</td><td class="num"><?= number_format((float)$invoice['sale'], 2) ?></td></tr>
            <tr><td>Cost</td><td class="num"><?= number_format((float)$invoice['cost'], 2) ?></td></tr>
            <tr><td>Expenses</td><td class="num"><?= number_format((float)$invoice['expenses'], 2) ?></td></tr>
            <tr><td>Income</td><td class="num"><?= number_format((float)$invoice['income'], 2) ?></td></tr>
            <tr><td>VAT</td><td class="num"><?= number_format((float)$invoice['vat'], 2) ?></td></tr>
            <tr><td>Discount</td><td class="num"><?= number_format((float)$invoice['discount'], 2) ?></td></tr>
            <tr><td>Balance</td><td class="num"><?= number_format((float)$invoice['balance'], 2) ?></td></tr>
            <tr><td>Paid</td><td class="num"><?= number_format((float)$invoice['paid'], 2) ?></td></tr>
        </table>

        <h3>Invoice Notes</h3>
        <p><?= nl2br(htmlspecialchars($invoice['notes'] ?? '-')) ?></p>

        <h3>Attachments</h3>
        <?php if (empty($gcsAttachments)): ?>
            <p>No attachments uploaded.</p>
        <?php else: ?>
            <div id="attachmentList" class="attachment-dropzone">
                <?php foreach ($gcsAttachments as $att): ?>
                    <div class="file-card">
                        <a href="<?= htmlspecialchars($att['url']) ?>" target="_blank" download>
                            📎 <?= htmlspecialchars($att['name']) ?>
                        </a>
                        
                        <!-- Correct Remove button -->
                        <a href="delete-file.php?invoice_id=<?= $id ?>&file=<?= urlencode(parse_url($att['url'], PHP_URL_PATH)) ?>" 
                        class="remove-btn">
                        ❌ Remove
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.attachment-dropzone {
    border: 2px dashed #ddd;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    background: #f9f9f9;
}

.file-card {
    background: #fff;
    border: 1px solid #ccc;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.remove-btn {
    background: #dc2626;
    border: none;
    color: #fff;
    font-size: 0.85em;
    border-radius: 4px;
    padding: 2px 6px;
    cursor: pointer;
}
.remove-btn:hover {
    background: #b91c1c;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
