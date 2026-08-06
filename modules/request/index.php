<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Material Requests';
requirePermission('request.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$employee_filter = $_GET['employee'] ?? '';

// Determine if user can approve (Store Manager/Admin)
$can_approve = hasPermission('issue.approve');

// Build query
$sql = "SELECT mr.*, m.material_name, u.full_name as employee_name, 
        u2.full_name as approved_by_name, mi.issue_no 
        FROM material_requests mr 
        INNER JOIN materials m ON mr.material_id = m.id 
        INNER JOIN users u ON mr.employee_id = u.id 
        LEFT JOIN users u2 ON mr.approved_by = u2.id 
        LEFT JOIN material_issues mi ON mr.id = mi.request_id 
        WHERE 1=1";
$params = [];

// If user cannot approve, show only their own requests
if (!$can_approve) {
    $sql .= " AND mr.employee_id = ?";
    $params[] = $_SESSION['user_id'];
}

if (!empty($search)) {
    $sql .= " AND (mr.request_no LIKE ? OR m.material_name LIKE ?)";
    $params = array_merge($params, array_fill(0, 2, "%$search%"));
}

if (!empty($status_filter)) {
    $sql .= " AND mr.status = ?";
    $params[] = $status_filter;
}

if ($can_approve && !empty($employee_filter)) {
    $sql .= " AND mr.employee_id = ?";
    $params[] = $employee_filter;
}

$sql .= " ORDER BY mr.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get employees for filter dropdown (only for approvers)
$employees = [];
if ($can_approve) {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users ORDER BY full_name");
    $stmt->execute();
    $employees = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Requests</h1>
    <?php if (hasPermission('request.create')): ?>
    <a href="<?php echo BASE_URL; ?>/modules/request/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Request
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search requests..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Issued" <?php echo $status_filter === 'Issued' ? 'selected' : ''; ?>>Issued</option>
                </select>
            </div>
            <?php if ($can_approve): ?>
            <div class="col-md-2">
                <select class="form-select" id="employeeFilter">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo $emp['id']; ?>" <?php echo $employee_filter == $emp['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-<?php echo $can_approve ? '5' : '7'; ?> text-end">
                <span class="text-muted"><?php echo count($requests); ?> requests found</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request No</th>
                        <th>Material</th>
                        <th>Requested Qty</th>
                        <th>Request Date</th>
                        <?php if ($can_approve): ?>
                        <th>Employee</th>
                        <?php endif; ?>
                        <th>Status</th>
                        <?php if ($can_approve): ?>
                        <th>Issue No</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($request['request_no']); ?></code></td>
                        <td><?php echo htmlspecialchars($request['material_name']); ?></td>
                        <td><?php echo number_format($request['requested_quantity'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                        <?php if ($can_approve): ?>
                        <td><?php echo htmlspecialchars($request['employee_name']); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php
                            $status_colors = [
                                'Pending' => 'warning',
                                'Approved' => 'info',
                                'Rejected' => 'danger',
                                'Issued' => 'success'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $status_colors[$request['status']]; ?>">
                                <?php echo htmlspecialchars($request['status']); ?>
                            </span>
                        </td>
                        <?php if ($can_approve): ?>
                        <td><?php echo $request['issue_no'] ? '<code>' . htmlspecialchars($request['issue_no']) . '</code>' : '-'; ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if ($can_approve && $request['status'] === 'Pending'): ?>
                            <button class="btn btn-sm btn-outline-success" onclick="approveRequest(<?php echo $request['id']; ?>)">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="rejectRequest(<?php echo $request['id']; ?>)">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="<?php echo $can_approve ? '8' : '6'; ?>" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No requests found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<?php if ($can_approve): ?>
<div class="modal fade" id="rejectRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectRequestForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="reject_request_id" name="request_id">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

document.getElementById('statusFilter').addEventListener('change', applyFilters);

<?php if ($can_approve): ?>
document.getElementById('employeeFilter').addEventListener('change', applyFilters);
<?php endif; ?>

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    let url = '?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status);
    
    <?php if ($can_approve): ?>
    const employee = document.getElementById('employeeFilter').value;
    url += '&employee=' + encodeURIComponent(employee);
    <?php endif; ?>
    
    window.location.href = url;
}

// View request
function viewRequest(id) {
    fetch('<?php echo BASE_URL; ?>/modules/request/get_request.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data) {
            let html = `
                <div class="row">
                    <div class="col-md-6"><strong>Request No:</strong></div>
                    <div class="col-md-6"><code>${data.request_no}</code></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Material:</strong></div>
                    <div class="col-md-6">${data.material_name}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Requested Quantity:</strong></div>
                    <div class="col-md-6">${parseFloat(data.requested_quantity).toFixed(2)}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Department:</strong></div>
                    <div class="col-md-6">${data.department || '-'}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Purpose:</strong></div>
                    <div class="col-md-6">${data.purpose || '-'}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Request Date:</strong></div>
                    <div class="col-md-6">${new Date(data.request_date).toLocaleDateString()}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Status:</strong></div>
                    <div class="col-md-6">${data.status}</div>
                </div>
            `;
            if (data.rejection_reason) {
                html += `<div class="row mt-2"><div class="col-md-6"><strong>Rejection Reason:</strong></div><div class="col-md-6 text-danger">${data.rejection_reason}</div></div>`;
            }
            document.getElementById('requestDetails').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
        }
    });
}

// Approve request
<?php if ($can_approve): ?>
function approveRequest(id) {
    if (confirm('Are you sure you want to approve this request?')) {
        const formData = new FormData();
        formData.append('request_id', id);
        formData.append('csrf_token', '<?php echo generateCsrfToken(); ?>');
        
        fetch('<?php echo BASE_URL; ?>/modules/issue/approve_reject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error approving request');
            }
        });
    }
}

function rejectRequest(id) {
    document.getElementById('reject_request_id').value = id;
    new bootstrap.Modal(document.getElementById('rejectRequestModal')).show();
}

document.getElementById('rejectRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    formData.append('action', 'reject');
    
    fetch('<?php echo BASE_URL; ?>/modules/issue/approve_reject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('rejectRequestModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Error rejecting request');
        }
    });
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
