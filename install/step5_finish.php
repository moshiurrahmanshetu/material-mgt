<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

// Check if coming from step 4
if (!isset($_SESSION['install_step']) || $_SESSION['install_step'] < 5) {
    $_SESSION['install_step'] = 4;
    header('Location: index.php');
    exit;
}

// Check if DB config and admin setup exist
if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_setup'])) {
    $_SESSION['install_step'] = 4;
    header('Location: index.php');
    exit;
}

$error = null;
$success = false;

// Write config file and create lock
try {
    $db_config = $_SESSION['db_config'];
    $admin_setup = $_SESSION['admin_setup'];
    
    // Generate config file content
    $config_content = "<?php\n";
    $config_content .="// Database Configuration\n";
    $config_content .= "if (!defined('DB_HOST')) { define('DB_HOST', '" . addslashes($db_config['host']) . "'); }\n";
    $config_content .= "if (!defined('DB_PORT')) { define('DB_PORT', '" . addslashes($db_config['port']) . "'); }\n";
    $config_content .= "if (!defined('DB_NAME')) { define('DB_NAME', '" . addslashes($db_config['name']) . "'); }\n";
    $config_content .= "if (!defined('DB_USER')) { define('DB_USER', '" . addslashes($db_config['user']) . "'); }\n";
    $config_content .= "if (!defined('DB_PASS')) { define('DB_PASS', '" . addslashes($db_config['pass']) . "'); }\n";
    $config_content .= "\n";
    $config_content .= "// Attempt database connection\n";
    $config_content .= "try {\n";
    $config_content .= "    \$dsn = \"mysql:host=\" . DB_HOST . \";port=\" . DB_PORT . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";\n";
    $config_content .= "    \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [\n";
    $config_content .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
    $config_content .= "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
    $config_content .= "        PDO::ATTR_EMULATE_PREPARES => false\n";
    $config_content .= "    ]);\n";
    $config_content .= "} catch (PDOException \$e) {\n";
    $config_content .= "    die('Database connection failed: ' . \$e->getMessage());\n";
    $config_content .= "}\n";
    
    // Write config file
    $config_path = __DIR__ . '/../config/db.php';
    $bytes_written = file_put_contents($config_path, $config_content);
    if ($bytes_written === false || $bytes_written === 0) {
        throw new Exception('Failed to write config file. Please check /config folder permissions.');
    }
    
    // Verify config file was written correctly
    if (!file_exists($config_path)) {
        throw new Exception('Config file was not created. Please check /config folder permissions.');
    }
    
    $written_content = file_get_contents($config_path);
    if (strpos($written_content, "define('DB_HOST'") === false) {
        throw new Exception('Config file was written but appears corrupted. Please check /config folder permissions.');
    }
    
    // Create lock file (only after config is verified)
    $lock_path = __DIR__ . '/installed.lock';
    $lock_content = date('Y-m-d H:i:s') . "\n";
    $lock_content .= "Installed by: " . $admin_setup['username'] . "\n";
    $lock_content .= "System Name: " . $admin_setup['system_name'] . "\n";
    
    if (file_put_contents($lock_path, $lock_content) === false) {
        throw new Exception('Failed to create lock file. Please check /install folder permissions.');
    }
    
    // Clear installer session data
    unset($_SESSION['install_step']);
    unset($_SESSION['db_config']);
    unset($_SESSION['admin_setup']);
    unset($_SESSION['sql_file']);
    
    $success = true;
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Material Management System</title>
    <link rel="stylesheet" href="assets/install-style.css">
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>Material Management System</h1>
            <p>Installation Wizard</p>
        </div>

        <div class="step-indicator">
            <div class="step completed">1</div>
            <div class="step-connector completed"></div>
            <div class="step completed">2</div>
            <div class="step-connector completed"></div>
            <div class="step completed">3</div>
            <div class="step-connector completed"></div>
            <div class="step completed">4</div>
            <div class="step-connector completed"></div>
            <div class="step completed">5</div>
        </div>

        <div class="installer-content">
            <?php if ($success): ?>
            <div class="success-message">
                <div class="success-icon">✓</div>
                <h2>Installation Complete!</h2>
                <p>Your Material Management System has been successfully installed.</p>
                
                <div class="installation-summary">
                    <h3>Installation Summary</h3>
                    <ul>
                        <li><strong>Database:</strong> Connected and configured</li>
                        <li><strong>SQL Import:</strong> Completed successfully</li>
                        <li><strong>Admin Account:</strong> Created/Updated</li>
                        <li><strong>System Name:</strong> <?php echo htmlspecialchars($admin_setup['system_name']); ?></li>
                        <li><strong>Company Name:</strong> <?php echo htmlspecialchars($admin_setup['company_name'] ?: 'Not set'); ?></li>
                    </ul>
                </div>

                <div class="security-notice">
                    <h3>Security Notice</h3>
                    <p>For extra security, you may now delete the <code>/install</code> folder. If you leave it, it will remain safely locked and inaccessible.</p>
                </div>

                <div class="form-actions">
                    <a href="../auth/login.php" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
            <?php else: ?>
            <div class="error-message">
                <div class="error-icon">✗</div>
                <h2>Installation Failed</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
                
                <div class="form-actions">
                    <a href="index.php?step=4" class="btn btn-primary">Try Again</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
