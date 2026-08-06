<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Add Role';
requirePermission('roles.create');

// Get all permissions grouped by module
$stmt = $pdo->query("SELECT * FROM permissions ORDER BY module_name, permission_key");
$all_permissions = $stmt->fetchAll();

// Group by module
$permissions_by_module = [];
foreach ($all_permissions as $perm) {
    $permissions_by_module[$perm['module_name']][] = $perm;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Role</h1>
    <a href="<?php echo BASE_URL; ?>/modules/roles/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Roles
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="roleForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role_name" class="form-label">Role Name *</label>
                    <input type="text" class="form-control" id="role_name" name="role_name" required>
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
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
            
            <hr>
            <h5 class="mb-3">Permissions</h5>
            
            <?php foreach ($permissions_by_module as $module => $permissions): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select_all_<?php echo md5($module); ?>" onchange="toggleModulePermissions('<?php echo md5($module); ?>')">
                        <label class="form-check-label fw-bold" for="select_all_<?php echo md5($module); ?>">
                            <?php echo htmlspecialchars($module); ?>
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($permissions as $perm): ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input module-permission-<?php echo md5($module); ?>" type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" id="perm_<?php echo $perm['id']; ?>">
                                <label class="form-check-label" for="perm_<?php echo $perm['id']; ?>">
                                    <?php echo htmlspecialchars($perm['permission_key']); ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/roles/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModulePermissions(moduleHash) {
    const selectAll = document.getElementById('select_all_' + moduleHash);
    const checkboxes = document.querySelectorAll('.module-permission-' + moduleHash);
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

document.getElementById('roleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/roles/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/roles/index.php';
        } else {
            showError(data.message || 'Error creating role');
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
