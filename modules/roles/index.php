<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Role & Permission Management';
requirePermission('roles.view');

// Get all roles with user count
$sql = "SELECT r.*, COUNT(u.id) as user_count 
        FROM roles r 
        LEFT JOIN users u ON r.id = u.role_id 
        GROUP BY r.id 
        ORDER BY r.role_name";
$stmt = $pdo->query($sql);
$roles = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Role & Permission Management</h1>
    <?php if (hasPermission('roles.create')): ?>
    <a href="<?php echo BASE_URL; ?>/modules/roles/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Role
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Users Assigned</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($role['role_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($role['description'] ?? '-'); ?></td>
                        <td><?php echo $role['user_count']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $role['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($role['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (hasPermission('roles.edit')): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="editRole(<?php echo $role['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (hasPermission('roles.delete') && $role['role_name'] !== 'Super Admin'): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRole(<?php echo $role['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No roles found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editRole(id) {
    window.location.href = '<?php echo BASE_URL; ?>/modules/roles/edit.php?id=' + id;
}

function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role?')) {
        const formData = new FormData();
        formData.append('role_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/roles/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting role');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
