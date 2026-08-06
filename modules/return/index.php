<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Material Returns';
requirePermission('return.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT mr.*, mi.issue_no, m.material_name, u.full_name as created_by_name 
        FROM material_returns mr 
        INNER JOIN material_issues mi ON mr.issue_id = mi.id 
        INNER JOIN materials m ON mr.material_id = m.id 
        LEFT JOIN users u ON mr.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (mr.return_no LIKE ? OR mi.issue_no LIKE ?)";
    $params = array_merge($params, array_fill(0, 2, "%$search%"));
}

if (!empty($date_from)) {
    $sql .= " AND mr.return_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND mr.return_date <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY mr.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$returns = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Returns</h1>
    <?php if (hasPermission('return.create')): ?>
    <a href="<?php echo BASE_URL; ?>/modules/return/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Return
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search returns..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From Date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To Date">
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-outline-secondary" onclick="applyFilters()">
                    <i class="bi bi-filter me-2"></i>Apply Filters
                </button>
                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                    <i class="bi bi-x-circle me-2"></i>Clear
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Return No</th>
                        <th>Issue No</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Return Date</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $return): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($return['return_no']); ?></code></td>
                        <td><code><?php echo htmlspecialchars($return['issue_no']); ?></code></td>
                        <td><?php echo htmlspecialchars($return['material_name']); ?></td>
                        <td><strong class="text-success">+<?php echo number_format($return['quantity'], 2); ?></strong></td>
                        <td><?php echo date('M d, Y', strtotime($return['return_date'])); ?></td>
                        <td><?php echo htmlspecialchars($return['created_by_name'] ?? 'System'); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/modules/return/view.php?id=<?php echo $return['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returns)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No returns found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let url = '?';
    const params = [];
    if (search) params.push('search=' + encodeURIComponent(search));
    if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
    if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
    
    window.location.href = url + params.join('&');
}

function clearFilters() {
    window.location.href = '?';
}

document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
