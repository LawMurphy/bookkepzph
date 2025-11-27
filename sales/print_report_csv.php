<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';

// ---------------------
// READ INPUTS
// ---------------------
$start = $_GET['start_date'] ?? null;
$end   = $_GET['end_date'] ?? null;
$selectedInvoices = $_GET['invoice_ids'] ?? [];

// Convert invoice_ids into array if passed as "5,7,9"
if (!is_array($selectedInvoices)) {
    if (!empty($selectedInvoices)) {
        $selectedInvoices = explode(',', $selectedInvoices);
    } else {
        $selectedInvoices = [];
    }
}

$invoices = [];
$filename = "";


// ===============================================================
//  CASE 1: INVOICE CHECKBOXES SELECTED (NO DATE REQUIRED)
// ===============================================================
if (!empty($selectedInvoices)) {

    $placeholders = implode(',', array_fill(0, count($selectedInvoices), '?'));

    $stmt = $pdo->prepare("
        SELECT 
            c.business_name AS customer_name,
            c.business_id AS customer_id,
            i.sale,
            i.cost,
            i.expenses,
            i.income,
            i.vat,
            i.discount,
            i.balance,
            i.invoice_ref,
            i.status,
            i.invoice_date,
            i.due_date,
            i.paid
        FROM invoices i
        LEFT JOIN customers c ON i.business_id = c.business_id
        WHERE i.id IN ($placeholders)
        ORDER BY i.invoice_date ASC
    ");
    $stmt->execute($selectedInvoices);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = "Invoices_Selected_" . date("Y-m-d_H-i") . ".csv";
} else {
    // same as above, just use LEFT JOIN with date range
    if (!$start || !$end) die("Please provide a valid date range OR select invoice checkboxes.");

    $stmt = $pdo->prepare("
        SELECT 
            c.business_name AS customer_name,
            c.business_id AS customer_id,
            i.sale,
            i.cost,
            i.expenses,
            i.income,
            i.vat,
            i.discount,
            i.balance,
            i.invoice_ref,
            i.status,
            i.invoice_date,
            i.due_date,
            i.paid
        FROM invoices i
        LEFT JOIN customers c ON i.business_id = c.business_id
        WHERE i.invoice_date BETWEEN :start AND :end
        ORDER BY i.invoice_date ASC
    ");
    $stmt->execute([':start' => $start, ':end' => $end]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = "Invoices_Report_" . $start . "_to_" . $end . ".csv";
}


// ===============================================================
// GENERATE CSV OUTPUT
// ===============================================================

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, [
    "CUSTOMER NAME",
    "CUSTOMER ID",
    "SALE",
    "COST",
    "EXPENSES",
    "INCOME",
    "VAT",
    "DISCOUNT",
    "BALANCE",
    "INVOICE REF #",
    "STATUS",
    "INVOICE DATE",
    "DUE DATE",
    "PAID"
]);

// Rows
foreach ($invoices as $inv) {
    fputcsv($output, [
        $inv['customer_name'],
        $inv['customer_id'],
        number_format((float)$inv['sale'], 2),
        number_format((float)$inv['cost'], 2),
        number_format((float)$inv['expenses'], 2),
        number_format((float)$inv['income'], 2),
        number_format((float)$inv['vat'], 2),
        number_format((float)$inv['discount'], 2),
        number_format((float)$inv['balance'], 2),
        $inv['invoice_ref'],
        $inv['status'],
        $inv['invoice_date'],
        $inv['due_date'],
        number_format((float)$inv['paid'], 2)
    ]);
}

fclose($output);
exit;
?>
