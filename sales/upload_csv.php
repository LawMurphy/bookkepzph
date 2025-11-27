<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');
ignore_user_abort(true);
set_time_limit(0);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Google\Cloud\Storage\StorageClient;

/*
|--------------------------------------------------------------------------
| 🔥 UNIVERSAL DATE NORMALIZER
|--------------------------------------------------------------------------
*/
function normalizeDate($value) {
    if (!$value || trim($value) === '') return null;

    // Excel numeric date
    if (is_numeric($value)) {
        try {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    $value = trim($value);

    // already correct (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    // detect dates with slashes
    if (strpos($value, '/') !== false) {
        $parts = explode('/', $value);
        if (count($parts) == 3) {
            $m = (int)$parts[0];
            $d = (int)$parts[1];
            $y = (int)$parts[2];

            // If month > 12 it means DD/MM/YYYY, swap
            if ($m > 12) {
                [$m, $d] = [$d, $m];
            }
            if (checkdate($m, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }
    }

    // attempt natural parse
    $ts = strtotime($value);
    if ($ts) return date('Y-m-d', $ts);

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

    $originalName = basename($_FILES['file']['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv','xls','xlsx'])) {
        $_SESSION['msg'] = "⚠️ Only CSV, XLS, XLSX allowed.";
        header("Location: active-invoices.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 Upload to Google Cloud Storage
    |--------------------------------------------------------------------------
    */
    $storage = new StorageClient([
        'keyFilePath' => __DIR__ . '/../credentials/bookkepz-key.json'
    ]);

    $bucketName = 'bookkepzfile';
    $bucket = $storage->bucket($bucketName);
    $objectPath = 'upload-csv/' . $originalName;

    $bucket->upload(fopen($_FILES['file']['tmp_name'], 'r'), ['name' => $objectPath]);
    $attachment_path = json_encode(["https://storage.googleapis.com/$bucketName/$objectPath"]);


    /*
    |--------------------------------------------------------------------------
    | 🔥 Convert XLS/XLSX → CSV (fastest)
    |--------------------------------------------------------------------------
    */
    $tempCSV = sys_get_temp_dir() . '/import_' . time() . '.csv';

    if ($ext === 'csv') {
        copy($_FILES['file']['tmp_name'], $tempCSV);
    } else {
        $reader = IOFactory::createReaderForFile($_FILES['file']['tmp_name']);
        $spreadsheet = $reader->load($_FILES['file']['tmp_name']);

        $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->setDelimiter(",");
        $writer->setEnclosure('"');
        $writer->setSheetIndex(0);
        $writer->save($tempCSV);

        unset($spreadsheet);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 Load customers dictionary once
    |--------------------------------------------------------------------------
    */
    $customers_by_id = [];
    $customers_by_name = [];

    $stmt = $pdo->query("SELECT business_id, business_name FROM customers");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $customers_by_id[strtolower($c['business_id'])] = $c['business_name'];
        $customers_by_name[strtolower($c['business_name'])] = $c['business_id'];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 Prepare insert/update
    |--------------------------------------------------------------------------
    */
    $sql = "
        INSERT INTO invoices (
            customer_name, business_id, sale, cost, expenses, income, vat,
            discount, balance, invoice_ref, status, invoice_date, due_date,
            paid, attachments
        )
        VALUES (
            :customer_name, :business_id, :sale, :cost, :expenses, :income, :vat,
            :discount, :balance, :invoice_ref, :status, :invoice_date, :due_date,
            :paid, :attachments
        )
        ON DUPLICATE KEY UPDATE
            customer_name = VALUES(customer_name),
            business_id   = VALUES(business_id),
            sale          = VALUES(sale),
            cost          = VALUES(cost),
            expenses      = VALUES(expenses),
            income        = VALUES(income),
            vat           = VALUES(vat),
            discount      = VALUES(discount),
            balance       = VALUES(balance),
            status        = VALUES(status),
            invoice_date  = VALUES(invoice_date),
            due_date      = VALUES(due_date),
            paid          = VALUES(paid),
            attachments   = VALUES(attachments)
    ";
    $stmtInsert = $pdo->prepare($sql);

    /*
    |--------------------------------------------------------------------------
    | 🔥 Stream CSV line by line (zero memory)
    |--------------------------------------------------------------------------
    */
    $handle = fopen($tempCSV, 'r');
    if (!$handle) {
        $_SESSION['msg'] = "❌ Cannot read CSV.";
        header("Location: active-invoices.php");
        exit;
    }

    // skip header
    fgetcsv($handle);

    /*
    |--------------------------------------------------------------------------
    | 🔥 Batch transactions
    |--------------------------------------------------------------------------
    */
    $batchSize = 300;
    $counter = 0;
    $inserted = 0;
    $replaced = 0;
    $skipped = 0;

    $pdo->exec("SET SESSION innodb_lock_wait_timeout = 120");

    $pdo->beginTransaction();

    while (($row = fgetcsv($handle)) !== false) {

        $customer_name = trim($row[0] ?? '');
        $customer_id   = trim($row[1] ?? '');
        $sale          = floatval($row[2] ?? 0);
        $cost          = floatval($row[3] ?? 0);
        $expenses      = floatval($row[4] ?? 0);
        $income        = floatval($row[5] ?? 0);
        $vat           = floatval($row[6] ?? 0);
        $discount      = floatval($row[7] ?? 0);
        $balance       = floatval($row[8] ?? 0);
        $invoice_ref   = trim($row[9] ?? '');
        $status        = trim($row[10] ?? 'Active');
        $invoice_date  = normalizeDate($row[11] ?? '');
        $due_date      = normalizeDate($row[12] ?? '');
        $paid          = floatval($row[13] ?? 0);

        if (!$invoice_ref) { $skipped++; continue; }

        // Auto-match customer
        if (!$customer_name && $customer_id && isset($customers_by_id[strtolower($customer_id)])) {
            $customer_name = $customers_by_id[strtolower($customer_id)];
            $replaced++;
        }

        if (!$customer_id && $customer_name && isset($customers_by_name[strtolower($customer_name)])) {
            $customer_id = $customers_by_name[strtolower($customer_name)];
            $replaced++;
        }

        if (!$customer_name && !$customer_id) { $skipped++; continue; }

        // Insert
        $stmtInsert->execute([
            ':customer_name' => $customer_name,
            ':business_id'   => $customer_id,
            ':sale'          => $sale,
            ':cost'          => $cost,
            ':expenses'      => $expenses,
            ':income'        => $income,
            ':vat'           => $vat,
            ':discount'      => $discount,
            ':balance'       => $balance,
            ':invoice_ref'   => $invoice_ref,
            ':status'        => $status,
            ':invoice_date'  => $invoice_date,
            ':due_date'      => $due_date,
            ':paid'          => $paid,
            ':attachments'   => $attachment_path
        ]);

        $inserted++;
        $counter++;

        // Batch commit
        if ($counter % $batchSize === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
        }
    }

    fclose($handle);

    // final commit
    $pdo->commit();

    $_SESSION['msg'] = "✅ Imported <strong>$inserted</strong> invoices. "
        . ($replaced ? "Auto-fixed $replaced customer IDs/names. " : "")
        . ($skipped ? "Skipped $skipped invalid rows." : "");

    header("Location: active-invoices.php");
    exit;

} else {
    $_SESSION['msg'] = "⚠️ Please upload a file.";
    header("Location: active-invoices.php");
    exit;
}
?>
