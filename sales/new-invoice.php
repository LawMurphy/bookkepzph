<?php
include '../config.php';
include '../includes/header.php';

// ✅ Load Composer autoloader
require '../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/new-invoice.css">

<?php
// Fetch customers
$stmt = $pdo->query("SELECT business_id, business_name FROM customers ORDER BY business_name ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last invoice ref
$stmtRef = $pdo->query("SELECT invoice_ref FROM invoices ORDER BY id DESC LIMIT 1");
$lastRef = $stmtRef->fetchColumn();

$today = date('Y-m-d');
$dateFormatted = date('d/m/Y', strtotime($today));

if ($lastRef && preg_match('/-(\d+)$/', $lastRef, $m)) {
    $nextNum = str_pad($m[1] + 1, 2, '0', STR_PAD_LEFT);
} else {
    $nextNum = '01';
}

$generated_ref = "INV-{$dateFormatted}-{$nextNum}";

// PROCESS SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_invoice'])) {

    $invoice_date  = $_POST['invoice_date'];
    $dateFormatted = date('d/m/Y', strtotime($invoice_date));

    $stmtLast = $pdo->query("SELECT invoice_ref FROM invoices ORDER BY id DESC LIMIT 1");
    $lastRef = $stmtLast->fetchColumn();

    if ($lastRef && preg_match('/-(\d+)$/', $lastRef, $match)) {
        $newCount = str_pad($match[1] + 1, 2, '0', STR_PAD_LEFT);
    } else {
        $newCount = '01';
    }

    $invoice_ref = "INV-{$dateFormatted}-{$newCount}";

    // Main data
    $business_id = $_POST['business_id'];
    $due_date    = $_POST['due_date'];
    $notes       = $_POST['notes'];

    // Finance Table — convert empty strings to 0
    $sale      = $_POST['sale'] !== '' ? $_POST['sale'] : 0;
    $cost      = $_POST['cost'] !== '' ? $_POST['cost'] : 0;
    $expenses  = $_POST['expenses'] !== '' ? $_POST['expenses'] : 0;
    $income    = $_POST['income'] !== '' ? $_POST['income'] : 0;
    $vat       = $_POST['vat'] !== '' ? $_POST['vat'] : 0;
    $discount  = $_POST['discount'] !== '' ? $_POST['discount'] : 0;
    $balance   = $_POST['balance'] !== '' ? $_POST['balance'] : 0;
    $paid      = $_POST['paid'] !== '' ? $_POST['paid'] : 0;


    // -------------------------------------
    // 🔥 Google Cloud Storage Upload
    // -------------------------------------
    $attachments = [];

    if (!empty($_FILES['attachments']['name'][0])) {

        // Initialize storage client
        $storage = new StorageClient([
            'keyFilePath' => __DIR__ . '/../credentials/bookkepz-key.json'
        ]);

        $bucketName = "bookkepzfile";
        $bucket = $storage->bucket($bucketName);

        foreach ($_FILES['attachments']['name'] as $i => $originalName) {

            $tmp = $_FILES['attachments']['tmp_name'][$i];

            // Keep original file name (avoid collisions if needed)
            $objectPath = "manual-transactions/" . basename($originalName);

            // Upload to GCS
            $bucket->upload(
                fopen($tmp, 'r'),
                [
                    'name' => $objectPath
                ]
            );

            // Public URL
            $publicUrl = "https://storage.googleapis.com/$bucketName/$objectPath";

            $attachments[] = $publicUrl;
        }
    }

    $attachments_json = json_encode($attachments);

    // Insert Invoice
    $stmt = $pdo->prepare("
        INSERT INTO invoices 
        (invoice_ref, invoice_date, business_id, due_date, notes, sale, cost, expenses, income, vat, discount, balance, paid, status, attachments)
        VALUES 
        (:invoice_ref, :invoice_date, :business_id, :due_date, :notes, :sale, :cost, :expenses, :income, :vat, :discount, :balance, :paid, 'Active', :attachments)
    ");

    $stmt->execute([
        ':invoice_ref' => $invoice_ref,
        ':invoice_date' => $invoice_date,
        ':business_id' => $business_id,
        ':due_date' => $due_date,
        ':notes' => $notes,
        ':sale' => $sale,
        ':cost' => $cost,
        ':expenses' => $expenses,
        ':income' => $income,
        ':vat' => $vat,
        ':discount' => $discount,
        ':balance' => $balance,
        ':paid' => $paid,
        ':attachments' => $attachments_json
    ]);

    header("Location: active-invoices.php");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">
<div class="invoice-card">
  <h2>Manual Transaction</h2>

  <div class="form-grid">
    <div>
      <label>Invoice Ref*</label>
      <input type="text" name="invoice_ref" id="invoice_ref" value="<?= $generated_ref ?>" readonly>
    </div>

    <div>
      <label>Invoice Date*</label>
      <input type="date" name="invoice_date" id="invoice_date" value="<?= date('Y-m-d') ?>">
    </div>

    <div>
      <label>Bill From*</label>
      <select name="bill_from">
        <option>Bookkepz</option>
      </select>
    </div>

    <div>
      <label>Bill/Deliver To*</label>
        <select name="business_id">
            <option value="">Select Customer</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['business_id'] ?>">
                    <?= htmlspecialchars($c['business_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
      </select>
    </div>

    <div>
      <label>Due Date*</label>
      <input type="date" name="due_date" value="<?= date('Y-m-d') ?>">
    </div>
  </div>

  <!-- NEW FINANCIAL TABLE -->
  <table class="items-table">
      <thead>
          <tr>
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
              <th>INVOICE DATE</th>
              <th>DUE DATE</th>
              <th>PAID</th>
          </tr>
      </thead>

      <tbody>
          <tr>
              <td><input type="text" name="customer_name" placeholder="Auto (based on customer)" readonly></td>
              <td><input type="text" name="business_id_display" placeholder="Auto" readonly></td>
              <td><input type="number" name="sale" step="0.01"></td>
              <td><input type="number" name="cost" step="0.01"></td>
              <td><input type="number" name="expenses" step="0.01"></td>
              <td><input type="number" name="income" step="0.01"></td>
              <td><input type="number" name="vat" step="0.01"></td>
              <td><input type="number" name="discount" step="0.01"></td>
              <td><input type="number" name="balance" step="0.01"></td>
              <td><input type="text" value="<?= $generated_ref ?>" readonly></td>
              <td><input type="text" value="Active" readonly></td>
              <td><input type="date" name="invoice_date_display" value="<?= date('Y-m-d') ?>" readonly></td>
              <td><input type="date" name="due_date_display" readonly></td>
              <td><input type="number" name="paid" step="0.01"></td>
          </tr>
      </tbody>
  </table>

  <div class="notes-section">
    <label>Invoice Notes</label>
    <textarea name="notes" placeholder="Add notes for the invoice"></textarea>
  </div>

  <div>
    <label>Attachments</label>

    <!-- DRAG & DROP BOX -->
    <div id="dropArea" class="attachments-box">
      <span class="upload-text">Drag & Drop files here or <strong>Click to Upload</strong></span>
      <input type="file" id="fileInput" name="attachments[]" multiple hidden>
    </div>

    <!-- PREVIEW LIST -->
    <div id="attachmentPreview" class="attachment-preview"></div>
  </div>

  <div class="invoice-actions-bottom">
    <button type="button" onclick="window.location='active-invoices.php'" class="btn-cancel">Cancel</button>
    <button type="submit" name="save_invoice" class="btn-primary">Save</button>
  </div>

</div>
</form>

<script>
// Sync customer ID + name to financial table
document.querySelector('select[name="business_id"]').addEventListener('change', function() {
    let businessId = this.value; // THIS IS '606E0B7F'
    let businessName = this.options[this.selectedIndex].text;

    document.querySelector('input[name="customer_name"]').value = businessName;

    // Display actual BUSINESS ID like 606E0B7F
    document.querySelector('input[name="business_id_display"]').value = businessId;

    document.querySelector('input[name="due_date_display"]').value =
        document.querySelector('input[name="due_date"]').value;
});


let selectedFiles = []; // store files before upload

const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const preview = document.getElementById("attachmentPreview");

const validTypes = ["jpg", "jpeg", "png", "pdf", "xlsx", "csv"];

// OPEN FILE INPUT ON CLICK
dropArea.addEventListener("click", () => fileInput.click());

// HANDLE FILE INPUT
fileInput.addEventListener("change", (e) => handleFiles(e.target.files));

// DRAG EVENTS
dropArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropArea.classList.add("dragover");
});

dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("dragover");
});

dropArea.addEventListener("drop", (e) => {
    e.preventDefault();
    dropArea.classList.remove("dragover");
    handleFiles(e.dataTransfer.files);
});

// PROCESS FILES
function handleFiles(files) {
    [...files].forEach(file => {
        if (selectedFiles.length >= 10) return alert("Maximum of 10 files only.");

        const ext = file.name.split('.').pop().toLowerCase();
        if (!validTypes.includes(ext)) {
            return alert("Invalid file type: " + file.name);
        }

        selectedFiles.push(file);
        displayFile(file, selectedFiles.length - 1);
    });

    updateInputFiles();
}

// DISPLAY FILE PREVIEW
function displayFile(file, index) {
    const fileItem = document.createElement("div");
    fileItem.classList.add("file-item");
    fileItem.setAttribute("data-index", index);

    const ext = file.name.split('.').pop().toLowerCase();

    // Show image if JPG/PNG
    if (["jpg", "jpeg", "png"].includes(ext)) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.classList.add("thumb");
        fileItem.appendChild(img);
    } else {
        const icon = document.createElement("i");
        icon.className = "fa-solid fa-file";
        fileItem.appendChild(icon);
    }

    // Filename
    const label = document.createElement("span");
    label.textContent = file.name;
    fileItem.appendChild(label);

    // File Size
    const size = document.createElement("span");
    size.textContent = (file.size / 1024).toFixed(1) + " KB";
    size.style.color = "#666";
    fileItem.appendChild(size);

    // REMOVE BUTTON
    const remove = document.createElement("button");
    remove.className = "remove-btn";
    remove.textContent = "X";
    remove.addEventListener("click", () => removeFile(index));

    fileItem.appendChild(remove);

    preview.appendChild(fileItem);
}

// REMOVE FILE
function removeFile(index) {
    selectedFiles.splice(index, 1); // remove file from array

    // refresh previews
    preview.innerHTML = "";
    selectedFiles.forEach((file, idx) => displayFile(file, idx));

    updateInputFiles();
}

// UPDATE input[type=file] before submitting the form
function updateInputFiles() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

// SYNC invoice date → due date
document.getElementById("invoice_date").addEventListener("change", function () {
    const inv = this.value;
    const due = document.querySelector('input[name="due_date"]');
    const tableDue = document.querySelector('input[name="due_date_display"]');

    due.value = inv;              // auto detect
    if (tableDue) tableDue.value = inv;
});

// SYNC due date → table due date
document.querySelector('input[name="due_date"]').addEventListener("change", function () {
    const val = this.value;
    const tableDue = document.querySelector('input[name="due_date_display"]');
    if (tableDue) tableDue.value = val;
});

// SYNC table due date → main due date
document.querySelector('input[name="due_date_display"]').addEventListener("change", function () {
    const val = this.value;
    const mainDue = document.querySelector('input[name="due_date"]');
    mainDue.value = val;
});

// Run once on page load
(function initDueDateSync() {
    const inv = document.getElementById("invoice_date").value;
    const due = document.querySelector('input[name="due_date"]');
    const tableDue = document.querySelector('input[name="due_date_display"]');

    due.value = inv;
    if (tableDue) tableDue.value = inv;
})();

// Update invoice ref date stamp
document.getElementById('invoice_date').addEventListener('change', function() {
    const date = new Date(this.value);
    const d = ('0'+date.getDate()).slice(-2);
    const m = ('0'+(date.getMonth()+1)).slice(-2);
    const y = date.getFullYear();

    const parts = document.getElementById('invoice_ref').value.split('-');
    const count = parts[2];

    document.getElementById('invoice_ref').value = `INV-${d}/${m}/${y}-${count}`;
});
</script>

<?php include '../includes/footer.php'; ?>
