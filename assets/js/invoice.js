document.addEventListener('DOMContentLoaded', function () {
  const addItemBtn = document.getElementById('addItem');
  const invoiceBody = document.getElementById('invoiceBody');
  const saveBtn = document.getElementById('saveInvoice');

  function updateTotals() {
    const rows = document.querySelectorAll('#invoiceBody tr');
    if (!rows.length) return;

    let subtotal = 0;
    rows.forEach(row => {
      const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
      const rate = parseFloat(row.querySelector('.item-rate')?.value) || 0;
      const amountCell = row.querySelector('.item-amount');
      const amount = qty * rate;
      if (amountCell) amountCell.textContent = `₱${amount.toFixed(2)}`;
      subtotal += amount;
    });

    const tax = subtotal * 0.12;
    const total = subtotal + tax;
    document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
    document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
  }

  // ✅ FIXED: cache the service options once to avoid duplication
  let cachedServiceOptions = '';
  const firstSelect = document.querySelector('.service-type');
  if (firstSelect) {
    cachedServiceOptions = Array.from(firstSelect.options)
      .map(
        opt => `<option value="${opt.value}" data-desc="${opt.dataset.desc || ''}" data-price="${opt.dataset.price || ''}">${opt.textContent}</option>`
      )
      .join('');
  } else {
    cachedServiceOptions = '<option value="">Select Service</option>';
  }

  if (addItemBtn && invoiceBody) {
    addItemBtn.addEventListener('click', () => {
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td><select class="service-type">${cachedServiceOptions}</select></td>
        <td><input type="text" class="item-desc" placeholder="Description"></td>
        <td><input type="number" class="item-qty" value="1" min="1"></td>
        <td><input type="number" class="item-rate" value="0" step="0.01"></td>
        <td class="item-amount">₱0.00</td>
        <td><input type="file" class="item-attachment" accept=".pdf,.xls,.xlsx,.doc,.docx"></td>
        <td><button type="button" class="btn-remove">✖</button></td>`;
      invoiceBody.appendChild(newRow);
      updateTotals();
    });
  }

  // ✅ keep event listeners clean
  invoiceBody?.addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove')) {
      e.target.closest('tr').remove();
      updateTotals();
    }
  });

  invoiceBody?.addEventListener('input', e => {
    if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-rate')) {
      updateTotals();
    }
  });

  // ✅ auto-fill rate/desc when service type changes
  invoiceBody?.addEventListener('change', e => {
    if (e.target.classList.contains('service-type')) {
      const select = e.target;
      const selected = select.options[select.selectedIndex];
      const price = parseFloat(selected.dataset.price) || 0;
      const desc = selected.dataset.desc || '';
      const row = select.closest('tr');
      const rateInput = row.querySelector('.item-rate');
      const descInput = row.querySelector('.item-desc');
      if (rateInput) rateInput.value = price.toFixed(2);
      if (descInput && !descInput.value) descInput.value = desc;
      updateTotals();
    }
  });

  // ✅ Save invoice (unchanged)
  let isSaving = false;
  if (saveBtn) {
    saveBtn.addEventListener('click', async () => {
      if (isSaving) return;
      isSaving = true;
      saveBtn.disabled = true;

      const customerSelect = document.getElementById('customerSelect');
      if (!customerSelect) return;

      const customerName = customerSelect.options[customerSelect.selectedIndex]?.text || '';
      const invoiceData = {
        invoiceNumber: document.getElementById('invoiceNumber')?.value || '',
        invoiceDate: document.getElementById('invoiceDate')?.value || '',
        customerName,
        items: [],
        subtotal: document.getElementById('subtotal')?.textContent.replace('₱', '').trim() || 0,
        tax: document.getElementById('tax')?.textContent.replace('₱', '').trim() || 0,
        total: document.getElementById('total')?.textContent.replace('₱', '').trim() || 0
      };

      const formData = new FormData();
      document.querySelectorAll('#invoiceBody tr').forEach((row, i) => {
        const item = {
          name: row.querySelector('.service-type')?.value || '',
          desc: row.querySelector('.item-desc')?.value || '',
          qty: row.querySelector('.item-qty')?.value || 0,
          rate: row.querySelector('.item-rate')?.value || 0
        };
        invoiceData.items.push(item);
        const file = row.querySelector('.item-attachment')?.files[0];
        if (file) formData.append(`file_${i}`, file);
      });

      formData.append('data', JSON.stringify(invoiceData));
      formData.append('action', 'save_invoice');

      try {
        const res = await fetch('../invoices/actions.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
          showNotification(`${result.message}<br>Invoice #: ${result.invoice_number}`, 'success');
          setTimeout(() => location.reload(), 2000);
        } else {
          showNotification(result.message, 'error');
        }
      } catch (err) {
        console.error(err);
        showNotification('Error saving invoice.', 'error');
      } finally {
        isSaving = false;
        saveBtn.disabled = false;
      }
    });
  }

  updateTotals();
});

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("addCustomerModal");
  const openBtn = document.getElementById("addCustomerBtn");
  const closeBtn = modal?.querySelector(".close-modal");
  const addForm = document.getElementById("addCustomerForm");
  const customerSelect = document.getElementById("customerSelect");

  if (!modal || !openBtn || !addForm) return;

  openBtn.addEventListener("click", () => (modal.style.display = "block"));
  closeBtn?.addEventListener("click", () => (modal.style.display = "none"));
  window.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });

  addForm.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(addForm);
    formData.append("action", "add_customer");

    try {
      const res = await fetch("../invoices/actions.php", { method: "POST", body: formData });
      const result = await res.json();

      if (result.status === "success") {
        const opt = document.createElement("option");
        opt.value = result.id;
        opt.textContent = result.name;
        customerSelect?.appendChild(opt);
        customerSelect.value = result.id;

        showNotification("Customer added successfully!", "success");
        modal.style.display = "none";
        addForm.reset();
      } else showNotification(result.message, "error");
    } catch {
      showNotification("Failed to add customer.", "error");
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const addBtn = document.getElementById("addServiceType");
  const modal = document.getElementById("addServiceModal");
  const form = document.getElementById("addServiceForm");
  const closeBtn = modal?.querySelector(".close-modal");

  if (!addBtn || !modal || !form) return;

  addBtn.addEventListener("click", () => (modal.style.display = "block"));
  closeBtn?.addEventListener("click", () => (modal.style.display = "none"));
  window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("action", "add_service_type");

    try {
      const res = await fetch("../invoices/actions.php", {
        method: "POST",
        body: formData
      });

      const result = await res.json();

      if (result.status === "success") {
        showNotification("Service Type added successfully!", "success");
        modal.style.display = "none";
        form.reset();
        setTimeout(() => location.reload(), 2000);
      } else {
        showNotification(result.message || "Failed to add service type.", "error");
      }
    } catch (err) {
      console.error(err);
      showNotification("Error saving Service Type.", "error");
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const removeBtn = document.getElementById('removeAttachmentBtn');
  const currentFile = document.getElementById('currentAttachment');
  const uploadSection = document.getElementById('uploadSection');
  const removeInput = document.getElementById('removeAttachmentInput');
  const fileInput = document.getElementById('new_attachment');
  const label = document.getElementById('uploadLabel');

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files?.length) {
        if (removeInput) removeInput.value = '0';
        const name = this.files[0].name;
        if (label) label.innerHTML = `<i class="fas fa-file"></i> ${name}`;
      } else if (label) {
        label.innerHTML = '<i class="fas fa-upload"></i> Upload New File';
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const qty = document.getElementById("qty");
  const rate = document.getElementById("rate");
  const subtotal = document.getElementById("subtotal");
  const tax = document.getElementById("tax");
  const total = document.getElementById("total");

  function calculateTotals() {
    const q = parseFloat(qty?.value) || 0;
    const r = parseFloat(rate?.value) || 0;
    const sub = q * r;
    const t = sub * 0.12;
    const tot = sub + t;

    if (subtotal) subtotal.value = sub.toFixed(2);
    if (tax) tax.value = t.toFixed(2);
    if (total) total.value = tot.toFixed(2);
  }

  if (qty && rate) {
    qty.addEventListener("input", calculateTotals);
    rate.addEventListener("input", calculateTotals);
    calculateTotals();
  }

  const removeBtn = document.getElementById("removeAttachmentBtn");
  const modal = document.getElementById("confirmRemoveModal");
  const confirmYes = document.getElementById("confirmRemoveYes");
  const confirmCancel = document.getElementById("confirmRemoveCancel");
  const removeInput = document.getElementById("removeAttachmentInput");
  const attachmentPreview = document.getElementById("currentAttachment");
  const uploadSection = document.getElementById("uploadSection");

  if (removeBtn && modal) {
    removeBtn.addEventListener("click", () => {
      modal.style.display = "flex";
    });

    confirmCancel?.addEventListener("click", () => {
      modal.style.display = "none";
    });

    confirmYes?.addEventListener("click", () => {
      modal.style.display = "none";
      if (removeInput) removeInput.value = "1";
      if (attachmentPreview) attachmentPreview.style.display = "none";
      if (uploadSection) uploadSection.style.display = "block";

      if (typeof showNotification === "function") {
        showNotification("Attachment marked for removal", "info", "center");
      }
    });
  }

  const params = new URLSearchParams(window.location.search);
  if (params.get("updated") === "1") {
    if (typeof showNotification === "function") {
      showNotification("Invoice updated successfully!", "success", "center");
    }
    setTimeout(() => {
      window.location.href = "invoice?id=" + params.get("id");
    }, 2000);
  }
});
