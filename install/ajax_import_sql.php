<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if DB config exists
if (!isset($_SESSION['db_config'])) {
    echo json_encode(['success' => false, 'message' => 'Database configuration not found. Please go back to Step 2.']);
    exit;
}

// Check if SQL file was uploaded
if (!isset($_SESSION['sql_file'])) {
    echo json_encode(['success' => false, 'message' => 'SQL file not found. Please upload a file first.']);
    exit;
}

$tmp_path = __DIR__ . '/tmp/' . $_SESSION['sql_file'];

if (!file_exists($tmp_path)) {
    echo json_encode(['success' => false, 'message' => 'SQL file not found on server.']);
    exit;
}

try {
    $db_config = $_SESSION['db_config'];
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Read SQL file
    $sql_content = file_get_contents($tmp_path);
    
    // Normalize line endings
    $sql_content = str_replace("\r\n", "\n", $sql_content);
    $sql_content = str_replace("\r", "\n", $sql_content);
    
    // Remove full-line comments and empty lines
    $lines = explode("\n", $sql_content);
    $cleaned_lines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed !== '' && !preg_match('/^--/', $trimmed) && !preg_match('/^#/', $trimmed)) {
            $cleaned_lines[] = $line;
        }
    }
    $sql_content = implode("\n", $cleaned_lines);
    
    // Split into statements - split on semicolon that's not inside a string
    // For this simple dump without stored procedures, we can split on semicolon
    $statements = explode(';', $sql_content);
    
    $executed = 0;
    $failed = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            $failed++;
            $errors[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'error' => $e->getMessage()
            ];
            // Stop on first critical error (like table creation failure)
            if (stripos($statement, 'CREATE TABLE') !== false) {
                throw $e;
            }
        }
    }
    
    // Delete the temp SQL file
    unlink($tmp_path);
    unset($_SESSION['sql_file']);
    
    // Check if critical tables exist
    $required_tables = ['users', 'roles', 'permissions', 'role_permissions', 'settings'];
    $stmt = $pdo->query("SHOW TABLES");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (!empty($missing_tables)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Import failed. Required tables are missing: ' . implode(', ', $missing_tables) . '. Executed: ' . $executed . ' statements.'
        ]);
        exit;
    }
    
    // Update session step
    $_SESSION['install_step'] = 4;
    
    if ($failed > 0) {
        echo json_encode([
            'success' => true,
            'message' => "$executed statements executed successfully, $failed failed (non-critical). Required tables present."
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => "$executed statements executed successfully."
        ]);
    }
    
} catch (PDOException $e) {
    // Delete the temp SQL file on error
    if (file_exists($tmp_path)) {
        unlink($tmp_path);
    }
    unset($_SESSION['sql_file']);
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Delete the temp SQL file on error
    if (file_exists($tmp_path)) {
        unlink($tmp_path);
    }
    unset($_SESSION['sql_file']);
    
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
