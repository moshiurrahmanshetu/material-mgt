<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Request Report';
requirePermission('reports.view');

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status_filter = $_GET['status'] ?? '';
$employee_filter = $_GET['employee'] ?? '';

// Get employees for dropdown
$stmt = $pdo->prepare("SELECT id, full_name FROM users ORDER BY full_name");
$stmt->execute();
$employees = $stmt->fetchAll();

// Build query
$sql = "SELECT mr.*, m.material_name, u.full_name as employee_name 
        FROM material_requests mr 
        INNER JOIN materials m ON mr.material_id = m.id 
        INNER JOIN users u ON mr.employee_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $sql .= " AND mr.request_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND mr.request_date <= ?";
    $params[] = $date_to;
}

if (!empty($status_filter)) {
    $sql .= " AND mr.status = ?";
    $params[] = $status_filter;
}

if (!empty($employee_filter)) {
    $sql .= " AND mr.employee_id = ?";
    $params[] = $employee_filter;
}

$sql .= " ORDER BY mr.request_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Request Report</h1>
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
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Issued" <?php echo $status_filter === 'Issued' ? 'selected' : ''; ?>>Issued</option>
                </select>
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
            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
        <div class="row">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/reports/request_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=request&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Results Card -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Material</th>
                        <th>Requested Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <?php
                    $status_colors = [
                        'Pending' => 'warning',
                        'Approved' => 'info',
                        'Rejected' => 'danger',
                        'Issued' => 'success'
                    ];
                    ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($request['request_no']); ?></code></td>
                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                        <td><?php echo htmlspecialchars($request['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['material_name']); ?></td>
                        <td><?php echo number_format($request['requested_quantity'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $status_colors[$request['status']]; ?>">
                                <?php echo htmlspecialchars($request['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No records found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
