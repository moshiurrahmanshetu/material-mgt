<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Materials';
requirePermission('materials.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT m.*, c.category_name, u.full_name as created_by_name 
        FROM materials m 
        INNER JOIN categories c ON m.category_id = c.id 
        LEFT JOIN users u ON m.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (m.material_code LIKE ? OR m.material_name LIKE ? OR m.description LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

if (!empty($category_filter)) {
    $sql .= " AND m.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND m.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// Get categories for filter dropdown
$stmt = $pdo->prepare("SELECT id, category_name FROM categories WHERE status = 'Active' ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Materials</h1>
    <?php if (hasPermission('materials.create')): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMaterialModal">
        <i class="bi bi-plus-circle me-2"></i>Add Material
    </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search materials..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-5 text-end">
                <span class="text-muted"><?php echo count($materials); ?> materials found</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Material Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <tr class="<?php echo $material['current_stock'] <= $material['minimum_stock'] ? 'table-warning' : ''; ?>">
                        <td><code><?php echo htmlspecialchars($material['material_code']); ?></code></td>
                        <td>
                            <strong><?php echo htmlspecialchars($material['material_name']); ?></strong>
                            <?php if ($material['current_stock'] <= $material['minimum_stock']): ?>
                            <span class="badge bg-danger ms-2">Low Stock</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td>
                            <span class="<?php echo $material['current_stock'] <= $material['minimum_stock'] ? 'text-danger fw-bold' : ''; ?>">
                                <?php echo number_format($material['current_stock'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($material['minimum_stock'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $material['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($material['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (hasPermission('materials.edit')): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="editMaterial(<?php echo $material['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('materials.delete')): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMaterial(<?php echo $material['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No materials found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Material Modal -->
<?php if (hasPermission('materials.create')): ?>
<div class="modal fade" id="createMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createMaterialForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="material_name" class="form-label">Material Name *</label>
                            <input type="text" class="form-control" id="material_name" name="material_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="unit" class="form-label">Unit *</label>
                            <select class="form-select" id="unit" name="unit" required>
                                <option value="Piece">Piece</option>
                                <option value="Kg">Kg</option>
                                <option value="Liter">Liter</option>
                                <option value="Meter">Meter</option>
                                <option value="Bag">Bag</option>
                                <option value="Box">Box</option>
                                <option value="Roll">Roll</option>
                                <option value="Packet">Packet</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="minimum_stock" class="form-label">Minimum Stock</label>
                            <input type="number" class="form-control" id="minimum_stock" name="minimum_stock" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="current_stock" class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="current_stock" name="current_stock" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Material</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Material Modal -->
<?php if (hasPermission('materials.edit')): ?>
<div class="modal fade" id="editMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMaterialForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="edit_material_id" name="material_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_material_name" class="form-label">Material Name *</label>
                            <input type="text" class="form-control" id="edit_material_name" name="material_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Category *</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_unit" class="form-label">Unit *</label>
                            <select class="form-select" id="edit_unit" name="unit" required>
                                <option value="Piece">Piece</option>
                                <option value="Kg">Kg</option>
                                <option value="Liter">Liter</option>
                                <option value="Meter">Meter</option>
                                <option value="Bag">Bag</option>
                                <option value="Box">Box</option>
                                <option value="Roll">Roll</option>
                                <option value="Packet">Packet</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_minimum_stock" class="form-label">Minimum Stock</label>
                            <input type="number" class="form-control" id="edit_minimum_stock" name="minimum_stock" step="0.01" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_current_stock" class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="edit_current_stock" name="current_stock" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Material</button>
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
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;
        window.location.href = '?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category) + '&status=' + encodeURIComponent(status);
    }
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    const search = document.getElementById('searchInput').value;
    const category = this.value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category) + '&status=' + encodeURIComponent(status);
});

document.getElementById('statusFilter').addEventListener('change', function() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = this.value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category) + '&status=' + encodeURIComponent(status);
});

// Create material
document.getElementById('createMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/materials/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createMaterialModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error creating material');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Edit material
function editMaterial(id) {
    fetch('<?php echo BASE_URL; ?>/modules/materials/get_material.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data) {
            document.getElementById('edit_material_id').value = data.id;
            document.getElementById('edit_material_name').value = data.material_name;
            document.getElementById('edit_category_id').value = data.category_id;
            document.getElementById('edit_unit').value = data.unit;
            document.getElementById('edit_minimum_stock').value = data.minimum_stock;
            document.getElementById('edit_current_stock').value = data.current_stock;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_status').value = data.status;
            new bootstrap.Modal(document.getElementById('editMaterialModal')).show();
        }
    });
}

document.getElementById('editMaterialForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/materials/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error updating material');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Delete material
function deleteMaterial(id) {
    if (confirm('Are you sure you want to delete this material?')) {
        const formData = new FormData();
        formData.append('material_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/materials/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting material');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
