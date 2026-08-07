<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

// Check if coming from step 2
if (!isset($_SESSION['install_step']) || $_SESSION['install_step'] < 3) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}

// Check if DB config exists
if (!isset($_SESSION['db_config'])) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}

// Handle file upload and import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a SQL file to upload.';
    } else {
        $file = $_FILES['sql_file'];
        
        // Validate file size (50MB max)
        $max_size = 50 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $error = 'File size exceeds maximum limit of 50MB. Please adjust upload_max_filesize and post_max_size in php.ini if needed.';
        } else {
            // Validate file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($extension !== 'sql') {
                $error = 'Invalid file type. Only .sql files are allowed.';
            } else {
                // Validate file content (check if it looks like SQL)
                $content = file_get_contents($file['tmp_name']);
                if (stripos($content, 'CREATE TABLE') === false && stripos($content, 'INSERT INTO') === false) {
                    $error = 'Invalid SQL file. The file does not appear to contain valid SQL statements.';
                } else {
                    // Generate random filename and move to tmp
                    $tmp_filename = uniqid('sql_import_', true) . '.sql';
                    $tmp_path = __DIR__ . '/tmp/' . $tmp_filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $tmp_path)) {
                        // Store in session for import
                        $_SESSION['sql_file'] = $tmp_filename;
                        
                        // Check if database already has tables
                        try {
                            $db_config = $_SESSION['db_config'];
                            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
                            $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            ]);
                            
                            $stmt = $pdo->query("SHOW TABLES");
                            $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (!empty($existing_tables)) {
                                // Database has tables - show warning
                                $has_tables = true;
                            }
                            // If database is empty, just show the normal import form (no auto-redirect)
                        } catch (PDOException $e) {
                            $error = 'Database connection failed: ' . $e->getMessage();
                        }
                    } else {
                        $error = 'Failed to upload file. Please check folder permissions.';
                    }
                }
            }
        }
    }
}

// Handle confirmation to proceed with existing tables
if (isset($_POST['confirm_import']) && isset($_SESSION['sql_file'])) {
    $_SESSION['install_step'] = 4;
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
            <div class="step completed">1</div>
            <div class="step-connector completed"></div>
            <div class="step completed">2</div>
            <div class="step-connector completed"></div>
            <div class="step active">3</div>
            <div class="step-connector"></div>
            <div class="step">4</div>
            <div class="step-connector"></div>
            <div class="step">5</div>
        </div>

        <div class="installer-content">
            <h2>Upload SQL File</h2>
            <p>Please upload your database export file (.sql). This file should contain all tables and seed data.</p>

            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($has_tables)): ?>
            <div class="alert alert-warning">
                <strong>Warning:</strong> This database already contains tables. Importing will not automatically drop them, and conflicting table names will cause errors.
                <br><br>
                <form method="POST">
                    <label>
                        <input type="checkbox" name="confirm_import" required>
                        I understand and want to proceed with the import
                    </label>
                    <br><br>
                    <button type="submit" class="btn btn-primary">Proceed with Import</button>
                    <a href="index.php?step=2" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
            <?php else: ?>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="sql_file">SQL File</label>
                    <input type="file" id="sql_file" name="sql_file" accept=".sql" required>
                    <small>Maximum file size: 50MB</small>
                </div>

                <div class="form-group" id="importProgress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div id="importStatus">Importing...</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="uploadBtn">Next: Admin Setup</button>
                    <a href="index.php?step=2" class="btn btn-secondary">Back</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!isset($has_tables)): ?>
    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('uploadBtn');
        const progress = document.getElementById('importProgress');
        const progressFill = document.getElementById('progressFill');
        const status = document.getElementById('importStatus');
        
        btn.disabled = true;
        progress.style.display = 'block';
        
        const formData = new FormData(this);
        formData.append('ajax', '1');
        
        fetch('ajax_import_sql.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                progressFill.style.width = '100%';
                status.innerHTML = '<span class="success">Import successful: ' + data.message + '</span>';
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            } else {
                status.innerHTML = '<span class="error">Import failed: ' + data.message + '</span>';
                btn.disabled = false;
            }
        })
        .catch(error => {
            status.innerHTML = '<span class="error">Error: ' + error + '</span>';
            btn.disabled = false;
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>
