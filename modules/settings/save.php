<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('settings.edit');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Handle logo deletion
if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == '1') {
    try {
        $stmt = $pdo->prepare("SELECT company_logo FROM settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch();
        
        if ($settings && !empty($settings['company_logo'])) {
            $logo_path = __DIR__ . '/../../uploads/' . $settings['company_logo'];
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE settings SET company_logo = NULL, updated_by = ?, updated_at = NOW() WHERE id = 1");
        $stmt->execute([$_SESSION['user_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'Update', 'Settings', 'Deleted company logo');
        
        echo json_encode(['success' => true, 'message' => 'Logo deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting logo: ' . $e->getMessage()]);
    }
    exit;
}

$system_name = trim($_POST['system_name'] ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$timezone = $_POST['timezone'] ?? 'Asia/Dhaka';
$date_format = $_POST['date_format'] ?? 'd-m-Y';

// Server-side validation
if (empty($system_name)) {
    echo json_encode(['success' => false, 'message' => 'System name is required']);
    exit;
}

if (!empty($email) && !isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Handle logo upload
$logo_filename = null;
if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['company_logo'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
        exit;
    }
    
    // Validate file size (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 2MB limit']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $logo_filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = __DIR__ . '/../../uploads/' . $logo_filename;
    
    // Create uploads directory if it doesn't exist
    if (!is_dir(__DIR__ . '/../../uploads')) {
        mkdir(__DIR__ . '/../../uploads', 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        echo json_encode(['success' => false, 'message' => 'Error uploading file']);
        exit;
    }
    
    // Delete old logo if exists
    $stmt = $pdo->prepare("SELECT company_logo FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch();
    
    if ($settings && !empty($settings['company_logo'])) {
        $old_logo_path = __DIR__ . '/../../uploads/' . $settings['company_logo'];
        if (file_exists($old_logo_path)) {
            unlink($old_logo_path);
        }
    }
}

try {
    // Build update query
    $sql = "UPDATE settings SET system_name = ?, company_name = ?, address = ?, phone = ?, email = ?, timezone = ?, date_format = ?, updated_by = ?, updated_at = NOW()";
    $params = [$system_name, $company_name, $address, $phone, $email, $timezone, $date_format, $_SESSION['user_id']];
    
    if ($logo_filename) {
        $sql .= ", company_logo = ?";
        $params[] = $logo_filename;
    }
    
    $sql .= " WHERE id = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Update', 'Settings', 'Updated system settings');
    
    $response = ['success' => true, 'message' => 'Settings saved successfully'];
    if ($logo_filename) {
        $response['logo_url'] = BASE_URL . '/uploads/' . $logo_filename;
    }
    
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error saving settings: ' . $e->getMessage()]);
}
