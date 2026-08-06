<?php
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'My Profile';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/functions.php';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid security token');
        redirect(BASE_URL . '/profile/index.php');
    }
    
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate inputs
    if (empty($full_name) || empty($email)) {
        setFlashMessage('danger', 'Full name and email are required');
        redirect(BASE_URL . '/profile/index.php');
    }
    
    if (!isValidEmail($email)) {
        setFlashMessage('danger', 'Invalid email format');
        redirect(BASE_URL . '/profile/index.php');
    }
    
    // Check if email already exists for another user
    if (emailExists($pdo, $email, $_SESSION['user_id'])) {
        setFlashMessage('danger', 'Email already exists');
        redirect(BASE_URL . '/profile/index.php');
    }
    
    // Update user
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $phone, $_SESSION['user_id']]);
    
    // Update session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Update', 'Profile', 'Profile updated');
    
    setFlashMessage('success', 'Profile updated successfully');
    redirect(BASE_URL . '/profile/index.php');
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$csrf_token = generateCsrfToken();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="<?php echo getAvatarUrl($user['avatar']); ?>" alt="Avatar" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['role_name']); ?></p>
                <p class="text-muted small"><?php echo htmlspecialchars($user['user_code']); ?></p>
                <a href="<?php echo BASE_URL; ?>/profile/update_avatar.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-camera me-2"></i>Change Avatar
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Edit Profile
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" name="role" 
                               value="<?php echo htmlspecialchars($user['role_name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_code" class="form-label">User Code</label>
                        <input type="text" class="form-control" id="user_code" name="user_code" 
                               value="<?php echo htmlspecialchars($user['user_code']); ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Profile
                    </button>
                    
                    <a href="<?php echo BASE_URL; ?>/profile/change_password.php" class="btn btn-outline-secondary">
                        <i class="bi bi-key me-2"></i>Change Password
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
