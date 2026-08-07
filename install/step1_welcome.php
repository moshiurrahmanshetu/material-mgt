<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

// Requirements check
$requirements = [
    'php_version' => [
        'name' => 'PHP Version >= 7.4',
        'check' => version_compare(PHP_VERSION, '7.4', '>='),
        'current' => PHP_VERSION,
        'required' => '7.4+'
    ],
    'pdo' => [
        'name' => 'PDO Extension',
        'check' => extension_loaded('pdo'),
        'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
        'required' => 'Enabled'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Extension',
        'check' => extension_loaded('pdo_mysql'),
        'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
        'required' => 'Enabled'
    ],
    'fileinfo' => [
        'name' => 'Fileinfo Extension',
        'check' => extension_loaded('fileinfo'),
        'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
        'required' => 'Enabled'
    ],
    'config_writable' => [
        'name' => '/config Folder Writable',
        'check' => is_writable(__DIR__ . '/../config'),
        'current' => is_writable(__DIR__ . '/../config') ? 'Writable' : 'Not Writable',
        'required' => 'Writable'
    ],
    'tmp_writable' => [
        'name' => '/install/tmp Folder Writable',
        'check' => is_writable(__DIR__ . '/tmp'),
        'current' => is_writable(__DIR__ . '/tmp') ? 'Writable' : 'Not Writable',
        'required' => 'Writable'
    ],
    'uploads_writable' => [
        'name' => '/uploads Folder Writable',
        'check' => is_writable(__DIR__ . '/../uploads'),
        'current' => is_writable(__DIR__ . '/../uploads') ? 'Writable' : 'Not Writable',
        'required' => 'Writable'
    ]
];

// Check if all requirements pass
$all_pass = true;
foreach ($requirements as $req) {
    if (!$req['check']) {
        $all_pass = false;
        break;
    }
}

// If all pass, allow proceeding to next step
if ($all_pass && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
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
            <div class="step active">1</div>
            <div class="step-connector"></div>
            <div class="step">2</div>
            <div class="step-connector"></div>
            <div class="step">3</div>
            <div class="step-connector"></div>
            <div class="step">4</div>
            <div class="step-connector"></div>
            <div class="step">5</div>
        </div>

        <div class="installer-content">
            <h2>Server Requirements Check</h2>
            <p>Please ensure your server meets the following requirements before proceeding.</p>

            <div class="requirements-list">
                <?php foreach ($requirements as $key => $req): ?>
                <div class="requirement-item <?php echo $req['check'] ? 'pass' : 'fail'; ?>">
                    <div class="requirement-icon">
                        <?php if ($req['check']): ?>
                            <span class="check-icon">✓</span>
                        <?php else: ?>
                            <span class="fail-icon">✗</span>
                        <?php endif; ?>
                    </div>
                    <div class="requirement-details">
                        <div class="requirement-name"><?php echo htmlspecialchars($req['name']); ?></div>
                        <div class="requirement-status">
                            Current: <?php echo htmlspecialchars($req['current']); ?> | Required: <?php echo htmlspecialchars($req['required']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$all_pass): ?>
            <div class="alert alert-error">
                <strong>Some requirements are not met.</strong> Please fix the issues above before proceeding.
                <br><br>
                Common fixes:
                <ul>
                    <li>For folder permissions: <code>chmod 755 /config /install/tmp /uploads</code> or <code>chmod 777</code> if needed</li>
                    <li>For missing extensions: Enable them in your php.ini or contact your hosting provider</li>
                    <li>For PHP version: Upgrade to PHP 7.4 or higher</li>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" class="btn btn-primary" <?php echo $all_pass ? '' : 'disabled'; ?>>
                    Next: Database Configuration
                </button>
            </form>
        </div>
    </div>
</body>
</html>
