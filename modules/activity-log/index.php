<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Activity Log';
requirePermission('activity_log.view');

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_filter = $_GET['user'] ?? '';
$module_filter = $_GET['module'] ?? '';
$action_filter = $_GET['action'] ?? '';

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Get users for dropdown
$stmt = $pdo->prepare("SELECT id, full_name FROM users ORDER BY full_name");
$stmt->execute();
$users = $stmt->fetchAll();

// Get distinct modules
$stmt = $pdo->query("SELECT DISTINCT module FROM activity_log ORDER BY module");
$modules = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get distinct actions
$stmt = $pdo->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
$actions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build count query
$count_sql = "SELECT COUNT(*) FROM activity_log WHERE 1=1";
$count_params = [];

if (!empty($date_from)) {
    $count_sql .= " AND DATE(created_at) >= ?";
    $count_params[] = $date_from;
}

if (!empty($date_to)) {
    $count_sql .= " AND DATE(created_at) <= ?";
    $count_params[] = $date_to;
}

if (!empty($user_filter)) {
    $count_sql .= " AND user_id = ?";
    $count_params[] = $user_filter;
}

if (!empty($module_filter)) {
    $count_sql .= " AND module = ?";
    $count_params[] = $module_filter;
}

if (!empty($action_filter)) {
    $count_sql .= " AND action = ?";
    $count_params[] = $action_filter;
}

$stmt = $pdo->prepare($count_sql);
$stmt->execute($count_params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Build data query
$sql = "SELECT al.*, u.full_name as user_name 
        FROM activity_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $sql .= " AND DATE(al.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(al.created_at) <= ?";
    $params[] = $date_to;
}

if (!empty($user_filter)) {
    $sql .= " AND al.user_id = ?";
    $params[] = $user_filter;
}

if (!empty($module_filter)) {
    $sql .= " AND al.module = ?";
    $params[] = $module_filter;
}

if (!empty($action_filter)) {
    $sql .= " AND al.action = ?";
    $params[] = $action_filter;
}

$sql .= " ORDER BY al.created_at DESC LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Build filter params for pagination links
$filter_params = $_GET;
unset($filter_params['page']);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Activity Log</h1>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-2 mb-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label for="user" class="form-label">User</label>
                <select class="form-select" id="user" name="user">
                    <option value="">All Users</option>
                    <?php foreach ($users as $usr): ?>
                    <option value="<?php echo $usr['id']; ?>" <?php echo $user_filter == $usr['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usr['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="module" class="form-label">Module</label>
                <select class="form-select" id="module" name="module">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $mod): ?>
                    <option value="<?php echo htmlspecialchars($mod); ?>" <?php echo $module_filter === $mod ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mod); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $act): ?>
                    <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $action_filter === $act ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($act); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
            </div>
        </form>
        <div class="row mt-2">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/activity-log/index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <span class="text-muted ms-3"><?php echo $total_records; ?> records found</span>
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
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></td>
                        <td><?php echo htmlspecialchars($activity['module']); ?></td>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><code><?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($activities)): ?>
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
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filter_params, ['page' => $page - 1])); ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <li class="page-item active"><span class="page-link"><?php echo $i; ?></span></li>
                <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filter_params, ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
                <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filter_params, ['page' => $page + 1])); ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <p class="text-center text-muted">Page <?php echo $page; ?> of <?php echo $total_pages; ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
