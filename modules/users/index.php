<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'User Management';
requirePermission('users.view');

// Get filters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Get roles for dropdown
$stmt = $pdo->prepare("SELECT id, role_name FROM roles WHERE status = 'Active' ORDER BY role_name");
$stmt->execute();
$roles = $stmt->fetchAll();

// Build query
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        INNER JOIN roles r ON u.role_id = r.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params = array_merge($params, array_fill(0, 3, "%$search%"));
}

if (!empty($role_filter)) {
    $sql .= " AND u.role_id = ?";
    $params[] = $role_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND u.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">User Management</h1>
    <?php if (hasPermission('users.create')): ?>
    <a href="<?php echo BASE_URL; ?>/modules/users/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add User
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="roleFilter">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['id']; ?>" <?php echo $role_filter == $role['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['role_name']); ?>
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
                <span class="text-muted"><?php echo count($users); ?> users found</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User Code</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($user['user_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td>
                            <?php if (hasPermission('users.edit')): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="resetPassword(<?php echo $user['id']; ?>)">
                                <i class="bi bi-key"></i>
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-sm btn-outline-<?php echo $user['status'] === 'Active' ? 'danger' : 'success'; ?>" onclick="toggleStatus(<?php echo $user['id']; ?>)">
                                <i class="bi bi-<?php echo $user['status'] === 'Active' ? 'person-x' : 'person-check'; ?>"></i>
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?php if (hasPermission('users.delete') && $user['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No users found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<?php if (hasPermission('users.edit')): ?>
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="reset_user_id" name="user_id">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Search and filter functionality
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

document.getElementById('roleFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    let url = '?';
    const params = [];
    if (search) params.push('search=' + encodeURIComponent(search));
    if (role) params.push('role=' + encodeURIComponent(role));
    if (status) params.push('status=' + encodeURIComponent(status));
    
    window.location.href = url + params.join('&');
}

function editUser(id) {
    window.location.href = '<?php echo BASE_URL; ?>/modules/users/edit.php?id=' + id;
}

function resetPassword(id) {
    document.getElementById('reset_user_id').value = id;
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    if (newPassword.length < 8) {
        alert('Password must be at least 8 characters');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/users/reset_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            alert('Password reset successfully');
        } else {
            alert(data.message || 'Error resetting password');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
});

function toggleStatus(id) {
    const formData = new FormData();
    formData.append('user_id', id);
    formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
    
    fetch('<?php echo BASE_URL; ?>/modules/users/toggle_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error toggling status');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('user_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/users/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting user');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
