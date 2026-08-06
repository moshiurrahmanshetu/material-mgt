<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Suppliers';
requirePermission('suppliers.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT s.*, u.full_name as created_by_name 
        FROM suppliers s 
        LEFT JOIN users u ON s.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.supplier_code LIKE ? OR s.supplier_name LIKE ? OR s.company LIKE ? OR s.contact_person LIKE ? OR s.email LIKE ?)";
    $params = array_fill(0, 5, "%$search%");
}

if (!empty($status_filter)) {
    $sql .= " AND s.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Suppliers</h1>
    <?php if (hasPermission('suppliers.create')): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSupplierModal">
        <i class="bi bi-plus-circle me-2"></i>Add Supplier
    </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search suppliers..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-5 text-end">
                <span class="text-muted"><?php echo count($suppliers); ?> suppliers found</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Supplier Code</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($supplier['supplier_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($supplier['company'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['contact_person'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['phone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $supplier['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($supplier['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (hasPermission('suppliers.edit')): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="editSupplier(<?php echo $supplier['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('suppliers.delete')): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSupplier(<?php echo $supplier['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No suppliers found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Supplier Modal -->
<?php if (hasPermission('suppliers.create')): ?>
<div class="modal fade" id="createSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSupplierForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_name" class="form-label">Supplier Name *</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Supplier Modal -->
<?php if (hasPermission('suppliers.edit')): ?>
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSupplierForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="edit_supplier_id" name="supplier_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_supplier_name" class="form-label">Supplier Name *</label>
                            <input type="text" class="form-control" id="edit_supplier_name" name="supplier_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="edit_company" name="company">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="edit_contact_person" name="contact_person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        const search = this.value;
        const status = document.getElementById('statusFilter').value;
        window.location.href = '?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status);
    }
});

document.getElementById('statusFilter').addEventListener('change', function() {
    const search = document.getElementById('searchInput').value;
    const status = this.value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status);
});

// Create supplier
document.getElementById('createSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/suppliers/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createSupplierModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error creating supplier');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Edit supplier
function editSupplier(id) {
    fetch('<?php echo BASE_URL; ?>/modules/suppliers/get_supplier.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data) {
            document.getElementById('edit_supplier_id').value = data.id;
            document.getElementById('edit_supplier_name').value = data.supplier_name;
            document.getElementById('edit_company').value = data.company || '';
            document.getElementById('edit_contact_person').value = data.contact_person || '';
            document.getElementById('edit_phone').value = data.phone || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('edit_status').value = data.status;
            new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
        }
    });
}

document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/suppliers/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editSupplierModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error updating supplier');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Delete supplier
function deleteSupplier(id) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        const formData = new FormData();
        formData.append('supplier_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/suppliers/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting supplier');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
