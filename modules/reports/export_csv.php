<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('reports.view');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=report_' . date('Y-m-d') . '.csv');

$report = $_GET['report'] ?? '';
$output = fopen('php://output', 'w');

switch ($report) {
    case 'purchase':
        fputcsv($output, ['Purchase No', 'Date', 'Supplier', 'Invoice No', 'Total Amount', 'Created By']);
        
        $sql = "SELECT pm.*, s.supplier_name, u.full_name as created_by_name 
                FROM purchase_master pm 
                INNER JOIN suppliers s ON pm.supplier_id = s.id 
                LEFT JOIN users u ON pm.created_by = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['date_from'])) {
            $sql .= " AND pm.purchase_date >= ?";
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $sql .= " AND pm.purchase_date <= ?";
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['supplier'])) {
            $sql .= " AND pm.supplier_id = ?";
            $params[] = $_GET['supplier'];
        }
        $sql .= " ORDER BY pm.purchase_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['purchase_no'],
                date('Y-m-d', strtotime($row['purchase_date'])),
                $row['supplier_name'],
                $row['invoice_number'] ?? '',
                $row['total_amount'],
                $row['created_by_name'] ?? 'System'
            ]);
        }
        break;
        
    case 'issue':
        fputcsv($output, ['Issue No', 'Date', 'Employee', 'Material', 'Issue Qty', 'Issued By']);
        
        $sql = "SELECT mi.*, m.material_name, u.full_name as employee_name, u2.full_name as issued_by_name 
                FROM material_issues mi 
                INNER JOIN materials m ON mi.material_id = m.id 
                INNER JOIN users u ON mi.employee_id = u.id 
                INNER JOIN users u2 ON mi.issued_by = u2.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['date_from'])) {
            $sql .= " AND mi.issue_date >= ?";
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $sql .= " AND mi.issue_date <= ?";
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['employee'])) {
            $sql .= " AND mi.employee_id = ?";
            $params[] = $_GET['employee'];
        }
        if (!empty($_GET['material'])) {
            $sql .= " AND mi.material_id = ?";
            $params[] = $_GET['material'];
        }
        $sql .= " ORDER BY mi.issue_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['issue_no'],
                date('Y-m-d', strtotime($row['issue_date'])),
                $row['employee_name'],
                $row['material_name'],
                $row['issue_quantity'],
                $row['issued_by_name']
            ]);
        }
        break;
        
    case 'request':
        fputcsv($output, ['Request No', 'Date', 'Employee', 'Material', 'Requested Qty', 'Status']);
        
        $sql = "SELECT mr.*, m.material_name, u.full_name as employee_name 
                FROM material_requests mr 
                INNER JOIN materials m ON mr.material_id = m.id 
                INNER JOIN users u ON mr.employee_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['date_from'])) {
            $sql .= " AND mr.request_date >= ?";
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $sql .= " AND mr.request_date <= ?";
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['status'])) {
            $sql .= " AND mr.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['employee'])) {
            $sql .= " AND mr.employee_id = ?";
            $params[] = $_GET['employee'];
        }
        $sql .= " ORDER BY mr.request_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['request_no'],
                date('Y-m-d', strtotime($row['request_date'])),
                $row['employee_name'],
                $row['material_name'],
                $row['requested_quantity'],
                $row['status']
            ]);
        }
        break;
        
    case 'supplier':
        fputcsv($output, ['Supplier Code', 'Name', 'Company', 'Status', 'Total Purchases', 'Total Amount']);
        
        $sql = "SELECT s.*, 
                COUNT(DISTINCT pm.id) as total_purchases,
                COALESCE(SUM(pm.total_amount), 0) as total_purchase_amount
                FROM suppliers s 
                LEFT JOIN purchase_master pm ON s.id = pm.supplier_id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['date_from'])) {
            $sql .= " AND (pm.purchase_date >= ? OR pm.id IS NULL)";
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $sql .= " AND (pm.purchase_date <= ? OR pm.id IS NULL)";
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $_GET['status'];
        }
        $sql .= " GROUP BY s.id ORDER BY s.supplier_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['supplier_code'],
                $row['supplier_name'],
                $row['company'] ?? '',
                $row['status'],
                $row['total_purchases'] ?: 0,
                $row['total_purchase_amount']
            ]);
        }
        break;
        
    case 'material':
        fputcsv($output, ['Material Code', 'Name', 'Category', 'Unit', 'Current Stock', 'Min Stock', 'Total Purchased', 'Total Issued']);
        
        $sql = "SELECT m.*, c.category_name,
                COALESCE(SUM(pi.quantity), 0) as total_purchased,
                COALESCE(SUM(mi.issue_quantity), 0) as total_issued
                FROM materials m 
                INNER JOIN categories c ON m.category_id = c.id 
                LEFT JOIN purchase_items pi ON m.id = pi.material_id
                LEFT JOIN material_issues mi ON m.id = mi.material_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['category'])) {
            $sql .= " AND m.category_id = ?";
            $params[] = $_GET['category'];
        }
        if (!empty($_GET['status'])) {
            $sql .= " AND m.status = ?";
            $params[] = $_GET['status'];
        }
        $sql .= " GROUP BY m.id ORDER BY m.material_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['material_code'],
                $row['material_name'],
                $row['category_name'],
                $row['unit'],
                $row['current_stock'],
                $row['minimum_stock'],
                $row['total_purchased'],
                $row['total_issued']
            ]);
        }
        break;
        
    case 'stock':
        fputcsv($output, ['Material Code', 'Name', 'Category', 'Unit', 'Current Stock', 'Minimum Stock', 'Stock Status']);
        
        $sql = "SELECT m.*, c.category_name 
                FROM materials m 
                INNER JOIN categories c ON m.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['category'])) {
            $sql .= " AND m.category_id = ?";
            $params[] = $_GET['category'];
        }
        $sql .= " ORDER BY m.material_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        while ($row = $stmt->fetch()) {
            if ($row['current_stock'] == 0) {
                $stock_status = 'Out of Stock';
            } elseif ($row['current_stock'] <= $row['minimum_stock']) {
                $stock_status = 'Low Stock';
            } else {
                $stock_status = 'In Stock';
            }
            
            if (empty($_GET['status']) || $stock_status === $_GET['status']) {
                fputcsv($output, [
                    $row['material_code'],
                    $row['material_name'],
                    $row['category_name'],
                    $row['unit'],
                    $row['current_stock'],
                    $row['minimum_stock'],
                    $stock_status
                ]);
            }
        }
        break;
        
    default:
        die('Invalid report type');
}

fclose($output);
exit;
