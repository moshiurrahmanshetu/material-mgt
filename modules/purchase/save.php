<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('purchase.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$purchase_date = $_POST['purchase_date'] ?? '';
$supplier_id = $_POST['supplier_id'] ?? 0;
$invoice_number = trim($_POST['invoice_number'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$items = $_POST['items'] ?? [];

// Server-side validation
if (empty($purchase_date)) {
    echo json_encode(['success' => false, 'message' => 'Purchase date is required']);
    exit;
}

if (empty($supplier_id)) {
    echo json_encode(['success' => false, 'message' => 'Supplier is required']);
    exit;
}

if (empty($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'At least one item is required']);
    exit;
}

// Validate purchase date not in future
$purchaseDateObj = new DateTime($purchase_date);
$today = new DateTime();
$today->setTime(0, 0, 0);
if ($purchaseDateObj > $today) {
    echo json_encode(['success' => false, 'message' => 'Purchase date cannot be in the future']);
    exit;
}

// Check if supplier exists and is active
$stmt = $pdo->prepare("SELECT id, status FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    echo json_encode(['success' => false, 'message' => 'Selected supplier does not exist']);
    exit;
}

if ($supplier['status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Selected supplier is not active']);
    exit;
}

// Validate all items
foreach ($items as $item) {
    $material_id = $item['material_id'] ?? 0;
    $quantity = $item['quantity'] ?? 0;
    $unit_price = $item['unit_price'] ?? 0;
    
    if (empty($material_id)) {
        echo json_encode(['success' => false, 'message' => 'Material is required for all items']);
        exit;
    }
    
    if (!is_numeric($quantity) || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0 for all items']);
        exit;
    }
    
    if (!is_numeric($unit_price) || $unit_price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Unit price must be greater than 0 for all items']);
        exit;
    }
    
    // Check if material exists and is active
    $stmt = $pdo->prepare("SELECT id, status FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $material = $stmt->fetch();
    
    if (!$material) {
        echo json_encode(['success' => false, 'message' => 'One or more selected materials do not exist']);
        exit;
    }
    
    if ($material['status'] !== 'Active') {
        echo json_encode(['success' => false, 'message' => 'One or more selected materials are not active']);
        exit;
    }
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate purchase number
    $prefix = 'PUR-';
    $stmt = $pdo->prepare("SELECT purchase_no FROM purchase_master WHERE purchase_no LIKE ? ORDER BY purchase_no DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastCode = $stmt->fetch();
    
    if ($lastCode) {
        $lastNumber = (int)substr($lastCode['purchase_no'], 4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $purchase_no = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    
    // Calculate total amount server-side
    $total_amount = 0;
    foreach ($items as $item) {
        $quantity = $item['quantity'];
        $unit_price = $item['unit_price'];
        $total_amount += ($quantity * $unit_price);
    }
    
    // Insert purchase master
    $stmt = $pdo->prepare("INSERT INTO purchase_master (purchase_no, purchase_date, supplier_id, invoice_number, total_amount, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$purchase_no, $purchase_date, $supplier_id, $invoice_number, $total_amount, $remarks, $_SESSION['user_id']]);
    $purchase_id = $pdo->lastInsertId();
    
    // Process each line item
    foreach ($items as $item) {
        $material_id = $item['material_id'];
        $quantity = $item['quantity'];
        $unit_price = $item['unit_price'];
        $line_total = $quantity * $unit_price;
        
        // Insert purchase item
        $stmt = $pdo->prepare("INSERT INTO purchase_items (purchase_id, material_id, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$purchase_id, $material_id, $quantity, $unit_price, $line_total]);
        
        // Get current stock
        $stmt = $pdo->prepare("SELECT current_stock FROM materials WHERE id = ?");
        $stmt->execute([$material_id]);
        $current_stock = $stmt->fetchColumn();
        
        // Calculate new stock
        $new_stock = $current_stock + $quantity;
        
        // Update material stock
        $stmt = $pdo->prepare("UPDATE materials SET current_stock = ? WHERE id = ?");
        $stmt->execute([$new_stock, $material_id]);
        
        // Insert stock movement
        $stmt = $pdo->prepare("INSERT INTO stock_movements (material_id, movement_type, reference_no, quantity_change, previous_stock, new_stock, remarks, created_by) VALUES (?, 'Purchase', ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$material_id, $purchase_no, $quantity, $current_stock, $new_stock, "Purchase: $purchase_no", $_SESSION['user_id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Create', 'Purchase', "Created purchase: $purchase_no");
    
    echo json_encode(['success' => true, 'message' => 'Purchase created successfully']);
    
} catch (PDOException $e) {
    // Roll back transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error saving purchase: ' . $e->getMessage()]);
}
