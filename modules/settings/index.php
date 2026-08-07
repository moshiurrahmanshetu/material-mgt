<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Settings';
requirePermission('settings.view');

// Get current settings
$stmt = $pdo->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();

if (!$settings) {
    setFlashMessage('danger', 'Settings not found');
    redirect(BASE_URL . '/dashboard/index.php');
}

// Common timezones
$timezones = [
    'Asia/Dhaka' => 'Asia/Dhaka (GMT+6)',
    'Asia/Kolkata' => 'Asia/Kolkata (GMT+5:30)',
    'UTC' => 'UTC (GMT+0)',
    'America/New_York' => 'America/New_York (GMT-5)',
    'America/Los_Angeles' => 'America/Los_Angeles (GMT-8)',
    'Europe/London' => 'Europe/London (GMT+0)',
    'Europe/Paris' => 'Europe/Paris (GMT+1)',
    'Asia/Tokyo' => 'Asia/Tokyo (GMT+9)',
    'Australia/Sydney' => 'Australia/Sydney (GMT+10)'
];

// Date formats with examples
$date_formats = [
    'd-m-Y' => 'd-m-Y (e.g., ' . date('d-m-Y') . ')',
    'Y-m-d' => 'Y-m-D (e.g., ' . date('Y-m-d') . ')',
    'm/d/Y' => 'm/d/Y (e.g., ' . date('m/d/Y') . ')',
    'd M, Y' => 'd M, Y (e.g., ' . date('d M, Y') . ')',
    'F d, Y' => 'F d, Y (e.g., ' . date('F d, Y') . ')'
];

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">System Settings</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="settingsForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <h5 class="mb-3">General Settings</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="system_name" class="form-label">System Name *</label>
                    <input type="text" class="form-control" id="system_name" name="system_name" value="<?php echo htmlspecialchars($settings['system_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="company_logo" class="form-label">Company Logo</label>
                <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                <small class="text-muted">Image only, max 2MB</small>
                <?php if (!empty($settings['company_logo'])): ?>
                <div class="mt-2">
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="deleteLogo()">Delete Logo</button>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                </div>
            </div>
            
            <hr>
            <h5 class="mb-3">Regional Settings</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="timezone" class="form-label">Timezone *</label>
                    <select class="form-select" id="timezone" name="timezone" required>
                        <?php foreach ($timezones as $tz => $label): ?>
                        <option value="<?php echo $tz; ?>" <?php echo $settings['timezone'] === $tz ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="date_format" class="form-label">Date Format *</label>
                    <select class="form-select" id="date_format" name="date_format" required>
                        <?php foreach ($date_formats as $fmt => $label): ?>
                        <option value="<?php echo $fmt; ?>" <?php echo $settings['date_format'] === $fmt ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            <div id="successMessage" class="alert alert-success d-none"></div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/settings/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Settings saved successfully');
            if (data.logo_url) {
                location.reload();
            }
        } else {
            showError(data.message || 'Error saving settings');
        }
    })
    .catch(error => {
        showError('Error: ' + error);
    });
});

function deleteLogo() {
    if (confirm('Are you sure you want to delete the company logo?')) {
        const formData = new FormData();
        formData.append('delete_logo', '1');
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/settings/save.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting logo');
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
    document.getElementById('successMessage').classList.add('d-none');
    setTimeout(() => {
        errorDiv.classList.add('d-none');
    }, 5000);
}

function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    successDiv.textContent = message;
    successDiv.classList.remove('d-none');
    document.getElementById('errorMessage').classList.add('d-none');
    setTimeout(() => {
        successDiv.classList.add('d-none');
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
