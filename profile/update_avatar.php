<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Change Avatar';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/functions.php';
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid security token');
        redirect(BASE_URL . '/profile/update_avatar.php');
    }
    
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        setFlashMessage('danger', 'Please select a file to upload');
        redirect(BASE_URL . '/profile/update_avatar.php');
    }
    
    $file = $_FILES['avatar'];
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        setFlashMessage('danger', 'File size exceeds maximum limit of 2MB');
        redirect(BASE_URL . '/profile/update_avatar.php');
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        setFlashMessage('danger', 'Invalid file type. Only JPG, PNG, and WEBP are allowed');
        redirect(BASE_URL . '/profile/update_avatar.php');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('avatar_') . '.' . $extension;
    $upload_path = AVATAR_UPLOAD_PATH . $filename;
    
    // Delete old avatar if not default
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['avatar'] && file_exists(AVATAR_UPLOAD_PATH . $user['avatar'])) {
        unlink(AVATAR_UPLOAD_PATH . $user['avatar']);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        setFlashMessage('danger', 'Failed to upload file');
        redirect(BASE_URL . '/profile/update_avatar.php');
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->execute([$filename, $_SESSION['user_id']]);
    
    // Update session
    $_SESSION['avatar'] = $filename;
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Update', 'Profile', 'Avatar updated');
    
    setFlashMessage('success', 'Avatar updated successfully');
    redirect(BASE_URL . '/profile/index.php');
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$csrf_token = generateCsrfToken();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Change Avatar</h1>
    <a href="<?php echo BASE_URL; ?>/profile/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Profile
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-camera me-2"></i>Change Avatar
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="text-center mb-4">
                        <img src="<?php echo getAvatarUrl($user['avatar']); ?>" alt="Current Avatar" 
                             id="avatarPreview" class="rounded-circle avatar-preview">
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Select New Avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" 
                               accept="image/jpeg,image/png,image/webp" onchange="previewAvatar(this)">
                        <small class="text-muted">Allowed formats: JPG, PNG, WEBP. Maximum size: 2MB</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-2"></i>Upload Avatar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
