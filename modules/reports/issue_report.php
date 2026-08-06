<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Issue Report';
requirePermission('reports.view');

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$employee_filter = $_GET['employee'] ?? '';
$material_filter = $_GET['material'] ?? '';

// Get employees and materials for dropdowns
$stmt = $pdo->prepare("SELECT id, full_name FROM users ORDER BY full_name");
$stmt->execute();
$employees = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT id, material_name FROM materials WHERE status = 'Active' ORDER BY material_name");
$stmt->execute();
$materials = $stmt->fetchAll();

// Build query
$sql = "SELECT mi.*, m.material_name, u.full_name as employee_name, u2.full_name as issued_by_name 
        FROM material_issues mi 
        INNER JOIN materials m ON mi.material_id = m.id 
        INNER JOIN users u ON mi.employee_id = u.id 
        INNER JOIN users u2 ON mi.issued_by = u2.id 
        WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $sql .= " AND mi.issue_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND mi.issue_date <= ?";
    $params[] = $date_to;
}

if (!empty($employee_filter)) {
    $sql .= " AND mi.employee_id = ?";
    $params[] = $employee_filter;
}

if (!empty($material_filter)) {
    $sql .= " AND mi.material_id = ?";
    $params[] = $material_filter;
}

$sql .= " ORDER BY mi.issue_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$issues = $stmt->fetchAll();

// Calculate total issued (for employee summary)
$total_issued = array_sum(array_column($issues, 'issue_quantity'));

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Issue Report</h1>
    <a href="<?php echo BASE_URL; ?>/modules/reports/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Reports
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="employee" class="form-label">Employee</label>
                <select class="form-select" id="employee" name="employee">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" <?php echo $employee_filter == $emp['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="material" class="form-label">Material</label>
                <select class="form-select" id="material" name="material">
                    <option value="">All Materials</option>
                    <?php foreach ($materials as $mat): ?>
                    <option value="<?php echo $mat['id']; ?>" <?php echo $material_filter == $mat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mat['material_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
        <div class="row">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/reports/issue_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=issue&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($employee_filter) && !empty($issues)): ?>
<div class="alert alert-info">
    <strong>Employee Summary:</strong> Total quantity issued to selected employee: <?php echo number_format($total_issued, 2); ?>
</div>
<?php endif; ?>

<!-- Results Card -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Issue No</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Material</th>
                        <th>Issue Qty</th>
                        <th>Issued By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($issue['issue_no']); ?></code></td>
                        <td><?php echo date('M d, Y', strtotime($issue['issue_date'])); ?></td>
                        <td><?php echo htmlspecialchars($issue['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($issue['material_name']); ?></td>
                        <td><strong class="text-danger">-<?php echo number_format($issue['issue_quantity'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($issue['issued_by_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($issues)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No records found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($issues)): ?>
                <tfoot>
                    <tr class="table-danger">
                        <td colspan="4" class="text-end"><strong>Total Issued:</strong></td>
                        <td><strong><?php echo number_format($total_issued, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
