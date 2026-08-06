<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Material Issues';
requirePermission('issue.view');

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Issues</h1>
</div>

<ul class="nav nav-tabs mb-4" id="issueTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
            Pending Approval
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
            Approved – Awaiting Issue
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="issued-tab" data-bs-toggle="tab" data-bs-target="#issued" type="button" role="tab">
            Issued History
        </button>
    </li>
</ul>

<div class="tab-content" id="issueTabsContent">
    <!-- Pending Approval Tab -->
    <div class="tab-pane fade show active" id="pending" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request No</th>
                                <th>Material</th>
                                <th>Requested Qty</th>
                                <th>Employee</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT mr.*, m.material_name, u.full_name as employee_name 
                                                  FROM material_requests mr 
                                                  INNER JOIN materials m ON mr.material_id = m.id 
                                                  INNER JOIN users u ON mr.employee_id = u.id 
                                                  WHERE mr.status = 'Pending' 
                                                  ORDER BY mr.created_at DESC");
                            $stmt->execute();
                            $pending = $stmt->fetchAll();
                            foreach ($pending as $req):
                            ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($req['request_no']); ?></code></td>
                                <td><?php echo htmlspecialchars($req['material_name']); ?></td>
                                <td><?php echo number_format($req['requested_quantity'], 2); ?></td>
                                <td><?php echo htmlspecialchars($req['employee_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                <td>
                                    <?php if (hasPermission('issue.approve')): ?>
                                    <button class="btn btn-sm btn-outline-success" onclick="approveRequest(<?php echo $req['id']; ?>)">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="rejectRequest(<?php echo $req['id']; ?>)">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?php echo $req['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pending)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-3">No pending requests</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Tab -->
    <div class="tab-pane fade" id="approved" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request No</th>
                                <th>Material</th>
                                <th>Requested Qty</th>
                                <th>Employee</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT mr.*, m.material_name, u.full_name as employee_name, 
                                                  u2.full_name as approved_by_name 
                                                  FROM material_requests mr 
                                                  INNER JOIN materials m ON mr.material_id = m.id 
                                                  INNER JOIN users u ON mr.employee_id = u.id 
                                                  LEFT JOIN users u2 ON mr.approved_by = u2.id 
                                                  WHERE mr.status = 'Approved' 
                                                  ORDER BY mr.approved_at DESC");
                            $stmt->execute();
                            $approved = $stmt->fetchAll();
                            foreach ($approved as $req):
                            ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($req['request_no']); ?></code></td>
                                <td><?php echo htmlspecialchars($req['material_name']); ?></td>
                                <td><?php echo number_format($req['requested_quantity'], 2); ?></td>
                                <td><?php echo htmlspecialchars($req['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($req['approved_by_name']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($req['approved_at'])); ?></td>
                                <td>
                                    <?php if (hasPermission('issue.create')): ?>
                                    <a href="<?php echo BASE_URL; ?>/modules/issue/create.php?request_id=<?php echo $req['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-right"></i> Issue
                                    </a>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?php echo $req['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($approved)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-3">No approved requests awaiting issue</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Issued History Tab -->
    <div class="tab-pane fade" id="issued" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Issue No</th>
                                <th>Request No</th>
                                <th>Material</th>
                                <th>Issued Qty</th>
                                <th>Employee</th>
                                <th>Issue Date</th>
                                <th>Issued By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT mi.*, mr.request_no, m.material_name, 
                                                  u.full_name as employee_name, u2.full_name as issued_by_name 
                                                  FROM material_issues mi 
                                                  INNER JOIN material_requests mr ON mi.request_id = mr.id 
                                                  INNER JOIN materials m ON mi.material_id = m.id 
                                                  INNER JOIN users u ON mi.employee_id = u.id 
                                                  INNER JOIN users u2 ON mi.issued_by = u2.id 
                                                  ORDER BY mi.created_at DESC");
                            $stmt->execute();
                            $issued = $stmt->fetchAll();
                            foreach ($issued as $iss):
                            ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($iss['issue_no']); ?></code></td>
                                <td><code><?php echo htmlspecialchars($iss['request_no']); ?></code></td>
                                <td><?php echo htmlspecialchars($iss['material_name']); ?></td>
                                <td><?php echo number_format($iss['issue_quantity'], 2); ?></td>
                                <td><?php echo htmlspecialchars($iss['employee_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($iss['issue_date'])); ?></td>
                                <td><?php echo htmlspecialchars($iss['issued_by_name']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/modules/issue/view.php?id=<?php echo $iss['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($issued)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-3">No issued history</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
<?php if (hasPermission('issue.approve')): ?>
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

<?php if (hasPermission('issue.approve')): ?>
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
