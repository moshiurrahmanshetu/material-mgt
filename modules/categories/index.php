<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Categories';
requirePermission('categories.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT c.*, u.full_name as created_by_name 
        FROM categories c 
        LEFT JOIN users u ON c.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.category_code LIKE ? OR c.category_name LIKE ? OR c.description LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}

if (!empty($status_filter)) {
    $sql .= " AND c.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Categories</h1>
    <?php if (hasPermission('categories.create')): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-circle me-2"></i>Add Category
    </button>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
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
                <span class="text-muted"><?php echo count($categories); ?> categories found</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Category Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($category['category_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 50)) . (strlen($category['description'] ?? '') > 50 ? '...' : ''); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $category['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($category['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($category['created_by_name'] ?? 'System'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                        <td>
                            <?php if (hasPermission('categories.edit')): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('categories.delete')): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No categories found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<?php if (hasPermission('categories.create')): ?>
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCategoryForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
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
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Category Modal -->
<?php if (hasPermission('categories.edit')): ?>
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
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
                    <button type="submit" class="btn btn-primary">Update Category</button>
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

// Create category
document.getElementById('createCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/categories/create.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createCategoryModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error creating category');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Edit category
function editCategory(id) {
    fetch('<?php echo BASE_URL; ?>/modules/categories/get_category.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data) {
            document.getElementById('edit_category_id').value = data.id;
            document.getElementById('edit_category_name').value = data.category_name;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_status').value = data.status;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    });
}

document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/categories/edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error updating category');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

// Delete category
function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        const formData = new FormData();
        formData.append('category_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/categories/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting category');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
