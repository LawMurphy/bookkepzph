<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../auth_check.php';

/*
|--------------------------------------------------------------------------
| Helper: build_url
|--------------------------------------------------------------------------
*/
function build_url($overrides = []) {
    return htmlspecialchars($_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, $overrides)));
}

/*
|--------------------------------------------------------------------------
| GET params + sanitization
|--------------------------------------------------------------------------
*/
$statusKey = $_GET['status'] ?? 'active';
$q = trim((string)($_GET['q'] ?? ''));
$sort = $_GET['sort'] ?? 'invoice_date';
$dir = strtoupper($_GET['dir'] ?? 'DESC');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

/*
|--------------------------------------------------------------------------
| Sorting: map allowed sort keys to actual DB columns (prevents injection)
|--------------------------------------------------------------------------
*/
$sortMap = [
    'customer_business_name' => 'c.business_name',
    'invoice_date' => 'i.invoice_date',
    'sale' => 'i.sale'
];
if (!isset($sortMap[$sort])) $sort = 'invoice_date';
$sortColumn = $sortMap[$sort];
$dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

/*
|--------------------------------------------------------------------------
| WHERE clause builder (use params)
|--------------------------------------------------------------------------
*/
$whereClauses = ["i.status = :status"];
$params = [':status' => $statusKey];

if ($q !== '') {
    // We search in customer name, business id and invoice_ref
    $whereClauses[] = "(c.business_name LIKE :q OR c.business_id LIKE :q OR i.invoice_ref LIKE :q)";
    $params[':q'] = "%$q%";
}

$whereSql = implode(' AND ', $whereClauses);

/*
|--------------------------------------------------------------------------
| Count total (for pagination)
|--------------------------------------------------------------------------
*/
$countSql = "
    SELECT COUNT(i.id) as cnt
    FROM invoices i
    LEFT JOIN customers c ON i.business_id = c.business_id
    WHERE $whereSql
";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

/*
|--------------------------------------------------------------------------
| Fetch page of invoices (only needed columns)
|--------------------------------------------------------------------------
*/
$dataSql = "
    SELECT
        i.id,
        i.customer_name,
        i.business_id,
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
        i.paid,
        c.business_name AS customer_business_name_sort
    FROM invoices i
    LEFT JOIN customers c ON i.business_id = c.business_id
    WHERE $whereSql
    ORDER BY $sortColumn $dir
    LIMIT :limit OFFSET :offset
";

$dataStmt = $pdo->prepare($dataSql);
// Bind where params
foreach ($params as $k => $v) $dataStmt->bindValue($k, $v);
// Bind pagination params (explicit types)
$dataStmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$dataStmt->execute();
$invoices = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="../assets/css/invoices.css">

<!-- Notifications -->
<?php if (!empty($_SESSION['msg'])): ?>
<div id="import-notification" style="
    position: fixed;
    top: 60px;
    right: 20px;
    background: #fef3c7;
    color: #78350f;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-weight: 500;
    z-index: 9999;
    max-width: 350px;
">
    <?= $_SESSION['msg'] ?>
</div>
<script>
setTimeout(() => {
    const notif = document.getElementById('import-notification');
    if(notif){
        notif.style.opacity = '0';
        setTimeout(() => notif.remove(), 500);
    }
}, 5000);
</script>
<?php unset($_SESSION['msg']); endif; ?>

<div class="invoice-page">

    <div class="invoice-top">
        <h2>Transactions</h2>
        <div class="top-buttons">
            <button class="btn primary" id="openUploadCsvBtn">⬆ Upload CSV</button>
            <button class="btn primary" id="printReportBtn">🖨 Print Report</button>
            <a href="new-invoice" class="btn primary">📝 Manual Transact</a>
        </div>
    </div>

    <!-- Search -->
    <form method="get" class="search-box">
        <input type="hidden" name="status" value="<?= htmlspecialchars($statusKey) ?>">
        <input type="search" name="q" placeholder="Search by customer or invoice ref"
               value="<?= htmlspecialchars($q) ?>" autocomplete="off">
        <button class="btn" type="submit">Search</button>
        <?php if ($q !== ''): ?>
            <a class="btn" href="<?= build_url(['q'=>'','page'=>1]) ?>">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Sort buttons -->
    <div class="sort-controls">
        <a class="btn" href="<?= build_url(['sort'=>'customer_business_name','dir'=>($dir==='ASC'?'DESC':'ASC')]) ?>">Sort by Customer Name</a>
        <a class="btn" href="<?= build_url(['sort'=>'invoice_date','dir'=>($dir==='ASC'?'DESC':'ASC')]) ?>">Sort by Invoice Date</a>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>CUSTOMER NAME</th>
                    <th>BUSINESS ID</th>
                    <th>SALE</th>
                    <th>COST</th>
                    <th>EXPENSES</th>
                    <th>INCOME</th>
                    <th>VAT</th>
                    <th>DISCOUNT</th>
                    <th>BALANCE</th>
                    <th>INVOICE REF #</th>
                    <th>STATUS</th>
                    <th>DUE DATE</th>
                    <th>PAID</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="15" class="empty">No invoices to display</td></tr>
                <?php else: foreach ($invoices as $inv): ?>
                    <tr>
                        <td><input type="checkbox" value="<?= (int)$inv['id'] ?>"></td>
                        <td><?= htmlspecialchars($inv['customer_business_name_sort'] ?? $inv['customer_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($inv['business_id'] ?? '-') ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['sale'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['cost'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['expenses'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['income'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['vat'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['discount'],2) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['balance'],2) ?></td>
                        <td><a href="view?id=<?= (int)$inv['id'] ?>"><?= htmlspecialchars($inv['invoice_ref']) ?></a></td>
                        <td><?= htmlspecialchars($inv['status']) ?></td>
                        <td><?= htmlspecialchars($inv['due_date']) ?></td>
                        <td style="text-align:right"><?= number_format((float)$inv['paid'],2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a class="page" href="<?= build_url(['page' => $page-1]) ?>">Prev</a>
                <?php endif; ?>

                <?php
                $window = 4; // show 4 numbers at a time
                $start = max(1, $page);
                $end = min($pages, $page + $window - 1);

                // adjust window if near the end
                if ($end - $start + 1 < $window) {
                    $start = max(1, $end - $window + 1);
                }

                for ($p = $start; $p <= $end; $p++):
                ?>
                    <?php if ($p == $page): ?>
                        <span class="page current"><?= $p ?></span>
                    <?php else: ?>
                        <a class="page" href="<?= build_url(['page' => $p]) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if($page < $pages): ?>
                    <a class="page" href="<?= build_url(['page' => $page+1]) ?>">Next</a>
                <?php endif; ?>

                <?php if($end < $pages): ?>
                    <a class="page" href="<?= build_url(['page' => $pages]) ?>">Last</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Print Report Modal -->
<div class="modal" id="printReportModal">
  <div class="modal-content">
    <h3>Financial Report Options</h3>
    <div id="modalWarning" class="modal-warning">
        Please select at least one invoice OR set a date range.
    </div>
    <form method="GET" action="print_report" target="_blank" id="reportForm" novalidate>
      <label>Start Date</label>
      <input type="date" name="start_date" required>
      <label>End Date</label>
      <input type="date" name="end_date" required>
      <label>Export Type</label>
      <select name="export_type" required>
        <option value="pdf">PDF Style (Printable)</option>
        <option value="csv">Excel / CSV File</option>
      </select>
      <div class="button-group">
        <button type="submit" class="btn primary">Generate Report</button>
        <button type="button" class="btn cancel-btn" id="closeModalBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Upload CSV Modal -->
<div class="modal" id="uploadCsvModal">
  <div class="modal-content" style="max-width:450px">
      <h3>Upload File</h3>

      <form method="POST" enctype="multipart/form-data" action="upload_csv.php">
          <input type="file" name="file" accept=".csv,.xls,.xlsx" required style="margin-bottom:10px;">
          <button type="submit" class="btn primary" style="width:100%;">Upload & Import</button>
      </form>

      <button class="btn cancel-btn" id="closeCsvModal" style="margin-top:10px; width:100%;">
          Cancel
      </button>
  </div>
</div>

<script>
const modal = document.getElementById('printReportModal');
const warningBox = document.getElementById('modalWarning');

// Open modal
document.getElementById('printReportBtn').addEventListener('click', () => {
    warningBox.classList.remove("show");
    modal.classList.add('show');
});

// Close modal
document.getElementById('closeModalBtn').addEventListener('click', () => {
    warningBox.classList.remove("show");
    modal.classList.remove('show');
});

// Close on outside click
modal.addEventListener('click', e => { 
    if(e.target === modal) {
        warningBox.classList.remove("show");
        modal.classList.remove('show');
    }
});

// Upload CSV Modal
const uploadModal = document.getElementById('uploadCsvModal');

document.getElementById('openUploadCsvBtn').addEventListener('click', () => {
    uploadModal.classList.add('show');
});

document.getElementById('closeCsvModal').addEventListener('click', () => {
    uploadModal.classList.remove('show');
});

uploadModal.addEventListener('click', e => {
    if (e.target === uploadModal) uploadModal.classList.remove('show');
});

// Form submit handler
document.getElementById('reportForm').addEventListener('submit', function(e){
    const form = e.target;
    const type = form.export_type.value;
    let selected = [];
    document.querySelectorAll("tbody input[type='checkbox']:checked").forEach(cb => selected.push(cb.value));

    if(selected.length > 0){
        form.start_date.removeAttribute("required");
        form.end_date.removeAttribute("required");
        let hidden = document.getElementById("selectedInvoices");
        if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "invoice_ids";
            hidden.id = "selectedInvoices";
            form.appendChild(hidden);
        }
        hidden.value = selected.join(",");
    } else if(!form.start_date.value || !form.end_date.value){
        e.preventDefault();
        warningBox.classList.add("show");
        setTimeout(() => warningBox.classList.remove("show"), 3000);
        return;
    }

    form.action = (type === 'csv') ? 'print_report_csv' : 'print_report';
});

// Select All / Deselect All
const checkAllBox = document.getElementById('checkAll');
checkAllBox.addEventListener('change', function() {
    document.querySelectorAll("tbody input[type='checkbox']").forEach(cb => cb.checked = checkAllBox.checked);
});
document.querySelectorAll("tbody input[type='checkbox']").forEach(cb => {
    cb.addEventListener('change', function() {
        if(!cb.checked){
            checkAllBox.checked = false;
        } else {
            const allChecked = Array.from(document.querySelectorAll("tbody input[type='checkbox']")).every(chk => chk.checked);
            checkAllBox.checked = allChecked;
        }
    });
});
</script>

<style>
.pagination {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 5px;
    flex-wrap: wrap;
}
.pagination .page {
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}
.pagination .page:hover {
    background-color: #f0f0f0;
}
.pagination .current {
    font-weight: bold;
    background-color: #007BFF;
    color: #fff;
    border-color: #007BFF;
}
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
