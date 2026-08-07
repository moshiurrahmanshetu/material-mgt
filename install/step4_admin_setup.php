<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

// Check if coming from step 3
if (!isset($_SESSION['install_step']) || $_SESSION['install_step'] < 4) {
    $_SESSION['install_step'] = 3;
    header('Location: index.php');
    exit;
}

// Check if DB config exists
if (!isset($_SESSION['db_config'])) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $system_name = trim($_POST['system_name'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    
    // Validate inputs
    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($system_name)) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        try {
            $db_config = $_SESSION['db_config'];
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Check if username/email already exists (excluding the default admin)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                $error = 'Username or email already exists.';
            } else {
                // Update the default admin user (user_code = 'USR-000001')
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, password = ? WHERE user_code = 'USR-000001'");
                $stmt->execute([$full_name, $email, $username, $hashed_password]);
                
                // Update settings (id = 1)
                $stmt = $pdo->prepare("UPDATE settings SET system_name = ?, company_name = ? WHERE id = 1");
                $stmt->execute([$system_name, $company_name]);
                
                // Store in session for final step
                $_SESSION['admin_setup'] = [
                    'full_name' => $full_name,
                    'email' => $email,
                    'username' => $username,
                    'system_name' => $system_name,
                    'company_name' => $company_name
                ];
                
                $_SESSION['install_step'] = 5;
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Pre-fill from session if available
$full_name = $_SESSION['admin_setup']['full_name'] ?? '';
$email = $_SESSION['admin_setup']['email'] ?? '';
$username = $_SESSION['admin_setup']['username'] ?? '';
$system_name = $_SESSION['admin_setup']['system_name'] ?? '';
$company_name = $_SESSION['admin_setup']['company_name'] ?? '';
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
            <div class="step active">4</div>
            <div class="step-connector"></div>
            <div class="step">5</div>
        </div>

        <div class="installer-content">
            <h2>Admin Account & Site Setup</h2>
            <p>Configure your administrator account and site details. This will update the default admin user and settings from the imported database.</p>

            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <h3>Administrator Account</h3>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small>Minimum 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <h3>Site Configuration</h3>

                <div class="form-group">
                    <label for="system_name">System Name *</label>
                    <input type="text" id="system_name" name="system_name" value="<?php echo htmlspecialchars($system_name); ?>" required>
                    <small>This will appear in the header and page titles</small>
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Next: Finish Installation</button>
                    <a href="index.php?step=3" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirm = this.value;
        
        if (password !== confirm) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
    </script>
</body>
</html>
