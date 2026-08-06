<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Edit User';
requirePermission('users.edit');

$user_id = $_GET['id'] ?? 0;

if (!$user_id) {
    setFlashMessage('danger', 'Invalid user ID');
    redirect(BASE_URL . '/modules/users/index.php');
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('danger', 'User not found');
    redirect(BASE_URL . '/modules/users/index.php');
}

// Get roles for dropdown
$stmt = $pdo->prepare("SELECT id, role_name FROM roles WHERE status = 'Active' ORDER BY role_name");
$stmt->execute();
$roles = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit User</h1>
    <a href="<?php echo BASE_URL; ?>/modules/users/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="userForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">Role *</label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Active" <?php echo $user['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $user['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label>Username:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/users/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/users/update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/users/index.php';
        } else {
            showError(data.message || 'Error updating user');
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
