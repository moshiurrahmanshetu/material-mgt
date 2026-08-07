<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

// Check if coming from step 1
if (!isset($_SESSION['install_step']) || $_SESSION['install_step'] < 2) {
    $_SESSION['install_step'] = 1;
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_port = trim($_POST['db_port'] ?? '3306');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');

    // Validate inputs
    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $error = 'Database host, name, and username are required.';
    } else {
        // Test connection
        try {
            $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Connection successful - store in session
            $_SESSION['db_config'] = [
                'host' => $db_host,
                'port' => $db_port,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass
            ];
            $_SESSION['install_step'] = 3;
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    }
}

// Pre-fill from session if available
$db_host = $_SESSION['db_config']['host'] ?? 'localhost';
$db_port = $_SESSION['db_config']['port'] ?? '3306';
$db_name = $_SESSION['db_config']['name'] ?? '';
$db_user = $_SESSION['db_config']['user'] ?? '';
$db_pass = $_SESSION['db_config']['pass'] ?? '';
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
            <div class="step active">2</div>
            <div class="step-connector"></div>
            <div class="step">3</div>
            <div class="step-connector"></div>
            <div class="step">4</div>
            <div class="step-connector"></div>
            <div class="step">5</div>
        </div>

        <div class="installer-content">
            <h2>Database Configuration</h2>
            <p>Please enter your database connection details. Make sure you have created an empty database before proceeding.</p>

            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="dbForm">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
                    <small>Usually "localhost"</small>
                </div>

                <div class="form-group">
                    <label for="db_port">Database Port</label>
                    <input type="text" id="db_port" name="db_port" value="<?php echo htmlspecialchars($db_port); ?>" required>
                    <small>Usually "3306"</small>
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
                    <small>The empty database you created</small>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
                </div>

                <div class="form-group">
                    <button type="button" id="testConnection" class="btn btn-secondary">Test Connection</button>
                    <span id="connectionResult"></span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="nextBtn" disabled>Next: Upload SQL File</button>
                    <a href="index.php?step=1" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('testConnection').addEventListener('click', function() {
        const btn = this;
        const result = document.getElementById('connectionResult');
        const nextBtn = document.getElementById('nextBtn');
        
        btn.disabled = true;
        result.innerHTML = '<span class="testing">Testing...</span>';
        nextBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_name', document.getElementById('db_name').value);
        formData.append('db_user', document.getElementById('db_user').value);
        formData.append('db_pass', document.getElementById('db_pass').value);
        
        fetch('ajax_test_connection.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                result.innerHTML = '<span class="success">Connection successful!</span>';
                nextBtn.disabled = false;
            } else {
                result.innerHTML = '<span class="error">' + data.message + '</span>';
            }
        })
        .catch(error => {
            btn.disabled = false;
            result.innerHTML = '<span class="error">Error: ' + error + '</span>';
        });
    });
    </script>
</body>
</html>
