<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'New Purchase';
requirePermission('purchase.create');

// Get suppliers and materials for dropdowns
$stmt = $pdo->prepare("SELECT id, supplier_name FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
$stmt->execute();
$suppliers = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, material_name, unit FROM materials WHERE status = 'Active' ORDER BY material_name");
$stmt->execute();
$materials = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Purchase</h1>
    <a href="<?php echo BASE_URL; ?>/modules/purchase/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="purchaseForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <!-- Header Section -->
            <h5 class="mb-3">Purchase Details</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="purchase_date" class="form-label">Purchase Date *</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="supplier_id" class="form-label">Supplier *</label>
                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_number" class="form-label">Invoice Number</label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number">
                </div>
            </div>
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
            </div>
            
            <hr>
            
            <!-- Line Items Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Items</h5>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="addRow()">
                    <i class="bi bi-plus-circle me-1"></i>Add Row
                </button>
            </div>
            
            <div class="table-responsive mb-3">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Material</th>
                            <th style="width: 15%;">Quantity</th>
                            <th style="width: 20%;">Unit Price</th>
                            <th style="width: 20%;">Total</th>
                            <th style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                </div>
                <div class="col-md-6 text-end">
                    <h4>Grand Total: <span id="grandTotal">0.00</span></h4>
                </div>
            </div>
            
            <hr>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/purchase/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Save Purchase
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const materials = <?php echo json_encode($materials); ?>;
let rowCount = 0;

// Add initial row
addRow();

function addRow() {
    rowCount++;
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = 'row_' + rowCount;
    
    let materialOptions = '<option value="">Select Material</option>';
    materials.forEach(m => {
        materialOptions += `<option value="${m.id}" data-unit="${m.unit}">${m.material_name} (${m.unit})</option>`;
    });
    
    row.innerHTML = `
        <td>
            <select class="form-select material-select" name="items[${rowCount}][material_id]" required onchange="calculateRowTotal(${rowCount})">
                ${materialOptions}
            </select>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[${rowCount}][quantity]" step="0.01" min="0.01" required onchange="calculateRowTotal(${rowCount})">
        </td>
        <td>
            <input type="number" class="form-control price-input" name="items[${rowCount}][unit_price]" step="0.01" min="0.01" required onchange="calculateRowTotal(${rowCount})">
        </td>
        <td>
            <input type="text" class="form-control total-input" name="items[${rowCount}][total]" readonly value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowCount})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
}

function removeRow(id) {
    const row = document.getElementById('row_' + id);
    if (row) {
        row.remove();
        calculateGrandTotal();
    }
}

function calculateRowTotal(rowId) {
    const row = document.getElementById('row_' + rowId);
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.total-input').value = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    const totals = document.querySelectorAll('.total-input');
    let grandTotal = 0;
    
    totals.forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Client-side validation
    const rows = document.querySelectorAll('#itemsBody tr');
    if (rows.length === 0) {
        showError('Please add at least one item');
        return;
    }
    
    let hasInvalidRow = false;
    rows.forEach(row => {
        const materialId = row.querySelector('.material-select').value;
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        
        if (!materialId || quantity <= 0 || price <= 0) {
            hasInvalidRow = true;
        }
    });
    
    if (hasInvalidRow) {
        showError('Please fill all item fields with valid values (quantity and price must be greater than 0)');
        return;
    }
    
    // Validate purchase date
    const purchaseDate = new Date(document.getElementById('purchase_date').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (purchaseDate > today) {
        showError('Purchase date cannot be in the future');
        return;
    }
    
    // Submit form
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/purchase/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/purchase/index.php';
        } else {
            showError(data.message || 'Error saving purchase');
        }
    })
    .catch(error => {
        showError('Error: ' + error);
    });
});

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
    setTimeout(() => {
        errorDiv.classList.add('d-none');
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
