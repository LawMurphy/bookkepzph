<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

// If user selected invoice checkboxes
$selected = isset($_GET['invoice_ids']) ? explode(",", $_GET['invoice_ids']) : [];

// VAT %
$vatPercent = 0.12;

// If invoices selected → fetch only those
if (!empty($selected)) {

    $placeholders = rtrim(str_repeat('?,', count($selected)), ',');
    $stmt = $pdo->prepare("
        SELECT i.*, c.business_name AS customer_name
        FROM invoices i
        LEFT JOIN customers c ON i.business_id = c.business_id
        WHERE i.id IN ($placeholders)
        ORDER BY i.invoice_date ASC
    ");
    $stmt->execute($selected);

    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Report Title
    $reportTitle = "SELECTED INVOICE REPORT";

} else {

    // Otherwise → require date range
    $start = $_GET['start_date'] ?? null;
    $end = $_GET['end_date'] ?? null;

    if (!$start || !$end) {
        die("Please provide a valid date range OR select invoices.");
    }

    // Fetch invoices in date range
    $stmt = $pdo->prepare("
        SELECT i.*, c.business_name AS customer_name
        FROM invoices i
        LEFT JOIN customers c ON i.business_id = c.business_id
        WHERE i.invoice_date BETWEEN :start AND :end
        ORDER BY i.invoice_date ASC
    ");
    $stmt->execute([':start'=>$start, ':end'=>$end]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Title for date range
    $reportTitle = date('F j, Y', strtotime($start));
    if ($start !== $end) {
        $reportTitle .= " - " . date('F j, Y', strtotime($end));
    }
}

// Compute totals
$totalBalance = 0;
foreach ($invoices as $inv) {
    $totalBalance += (float)$inv['balance'];
}

$vat = $totalBalance * $vatPercent;
$totalWithVat = $totalBalance + $vat;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Financial Report</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="img/png" href="../assets/img/bookkepz_logo.png">
<style>
body { 
    margin: 0; 
    padding: 30px; 
    font-family: 'Inter', sans-serif; 
    background: #f3f4f6; 
    color: #1f2937; 
}

@media print { 
    body { -webkit-print-color-adjust: exact; }
}

/* Header Logo */
.logo {
    width: auto;
    height: auto;
    margin: 0 auto 10px;
    text-align: center;
}

.logo img {
    width: 90px;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* Title */
.company-info h2 { 
    text-align: center; 
    margin: 5px 0; 
    font-size: 22px; 
    color: #111827; 
}
.report-title { text-align: center; margin-bottom: 20px; }
.report-title h1 { font-size: 28px; color: #2563eb; margin: 10px 0 5px; }
.report-title h3 { font-size: 16px; color: #374151; margin: 5px 0; }

/* FIX: Remove scroll and force fit */
.table-container {
    margin-top: 30px;
    width: 100%;
    overflow: visible !important;
}

/* FIX: Auto adjust table width */
.report-table {
    width: 100% !important;
    border-collapse: collapse;
    background: white;
    table-layout: fixed; /* IMPORTANT */
}

.report-table th {
    background: #2563eb;
    color: white;
    padding: 8px 5px;
    font-size: 10px;
    text-align: center;
    border: 1px solid #d1d5db;
}

.report-table td {
    padding: 6px 4px;
    font-size: 10px;
    border: 1px solid #e5e7eb;
    text-align: center;
    word-wrap: break-word;
}

/* PRINT OPTIMIZATION */
@media print {
    .report-table th,
    .report-table td {
        font-size: 9px !important;
        padding: 4px !important; 
        white-space: normal !important; /* wrap text */
    }
}

/* Totals Box */
.total-box {
    width: 280px;
    margin-top: 30px;
    margin-left: auto;
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
}
.total-box div { margin: 6px 0; font-size: 14px; }
.total-box .grand { 
    font-weight: 600; 
    margin-top: 10px; 
    border-top: 1px solid #d1d5db; 
    padding-top: 8px; 
}

</style>
</head>
<body>
    <div class="logo">
        <img src="../assets/img/bookkepz_logo.png" alt="Bookkepz Logo">
    </div>

    <div class="company-info">
        <h2>BOOKKEPZ</h2>
    </div>

<div class="report-title">
    <h1>FINANCIAL STATEMENT</h1>
    <h3><?= strtoupper($reportTitle) ?> REPORT</h3>
</div>

<div class="table-container">
    <table class="report-table">
    <thead>
        <tr>
            <th>CUSTOMER NAME</th>
            <th>CUSTOMER ID</th>
            <th>SALE</th>
            <th>COST</th>
            <th>EXPENSES</th>
            <th>INCOME</th>
            <th>VAT</th>
            <th>DISCOUNT</th>
            <th>BALANCE</th>
            <th>INVOICE REF #</th>
            <th>STATUS</th>
            <th>INVOICE DATE</th>
            <th>DUE DATE</th>
            <th>PAID</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($invoices as $inv): ?>
        <tr>
            <td><?= $inv['customer_name'] ?: '-' ?></td>
            <td><?= $inv['business_id'] ?></td>
            <td><?= number_format($inv['sale'], 2) ?></td>
            <td><?= number_format($inv['cost'], 2) ?></td>
            <td><?= number_format($inv['expenses'], 2) ?></td>
            <td><?= number_format($inv['income'], 2) ?></td>
            <td><?= number_format($inv['vat'], 2) ?></td>
            <td><?= number_format($inv['discount'], 2) ?></td>
            <td><?= number_format($inv['balance'], 2) ?></td>
            <td><?= $inv['invoice_ref'] ?></td>
            <td><?= ucfirst($inv['status']) ?></td>
            <td><?= $inv['invoice_date'] ?></td>
            <td><?= $inv['due_date'] ?></td>
            <td><?= $inv['paid'] ? "YES" : "NO" ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="total-box">
    <div><strong>Total Balance:</strong> <?= number_format($totalBalance, 2) ?></div>
    <div><strong>VAT (<?= $vatPercent * 100 ?>%):</strong> <?= number_format($vat, 2) ?></div>
    <div class="grand"><strong>Total Amount With VAT:</strong> <?= number_format($totalWithVat, 2) ?></div>
</div>

<script>
window.onload = () => { window.print(); };
</script>

</body>
</html>
