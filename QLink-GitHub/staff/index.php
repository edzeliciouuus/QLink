<?php
require_once '../includes/config.php';
require_once '../includes/Auth.php';

// Check if user is logged in and has staff/admin role
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$auth = new Auth();
if (!in_array($_SESSION['role'], ['staff', 'admin'])) {
    header('Location: ../dashboard.php');
    exit();
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Console - QLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-person-gear"></i> Staff Console
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../api/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">Queue Management Console</h1>
                <p class="text-muted">Manage queues and serve customers efficiently</p>
            </div>
        </div>

        <!-- Department Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Select Department</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select" id="departmentSelect">
                            <option value="">Choose a department...</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Management -->
        <div class="row" id="queueManagementSection" style="display: none;">
            <!-- Current Status -->
            <div class="col-lg-4 mb-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Current Status</h6>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-2" id="nowServing">-</h2>
                        <p class="text-muted mb-3">Now Serving</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" id="callNextBtn">
                                <i class="bi bi-arrow-right-circle"></i> Call Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next in Line -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-list-ol"></i> Next in Line</h6>
                    </div>
                    <div class="card-body">
                        <div id="nextInLine">
                            <div class="text-center text-muted">
                                <i class="bi bi-arrow-clockwise"></i> Loading queue...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currently Serving -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-person-check"></i> Currently Serving</h6>
                    </div>
                    <div class="card-body">
                        <div id="currentlyServing">
                            <div class="text-center text-muted">
                                <i class="bi bi-arrow-clockwise"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Department Selected -->
        <div class="row" id="noDepartmentSection">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Select a Department</h4>
                        <p class="text-muted">Choose a department above to start managing queues</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modals -->
    
    <!-- Skip Modal -->
    <div class="modal fade" id="skipModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Skip Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to skip <strong id="skipCustomerName">this customer</strong>?</p>
                    <p class="text-muted small">They will be moved back to the waiting list.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmSkipBtn">Skip Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Done Modal -->
    <div class="modal fade" id="doneModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Done</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark <strong id="doneCustomerName">this customer</strong> as served?</p>
                    <p class="text-muted small">This will complete their queue session.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmDoneBtn">Mark Done</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Global variables
        let currentDepartment = null;
        let updateInterval = null;
        let skipQueueId = null;
        let doneQueueId = null;

        // Initialize staff console
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartments();
            setupEventListeners();
        });

        // Load available departments
        function loadDepartments() {
            fetch('../api/queues/departments.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('departmentSelect');
                        select.innerHTML = '<option value="">Choose a department...</option>';
                        data.departments.forEach(dept => {
                            if (dept.is_active) {
                                const option = document.createElement('option');
                                option.value = dept.dept_id;
                                option.textContent = dept.name;
                                select.appendChild(option);
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading departments:', error));
        }

        // Setup event listeners
        function setupEventListeners() {
            // Department selection change
            document.getElementById('departmentSelect').addEventListener('change', function() {
                const deptId = this.value;
                if (deptId) {
                    currentDepartment = deptId;
                    showQueueManagement();
                    loadQueueData();
                    startUpdates();
                } else {
                    hideQueueManagement();
                    stopUpdates();
                }
            });

            // Call next button
            document.getElementById('callNextBtn').addEventListener('click', function() {
                callNext();
            });
        }

        // Show queue management section
        function showQueueManagement() {
            document.getElementById('queueManagementSection').style.display = 'block';
            document.getElementById('noDepartmentSection').style.display = 'none';
        }

        // Hide queue management section
        function hideQueueManagement() {
            document.getElementById('queueManagementSection').style.display = 'none';
            document.getElementById('noDepartmentSection').style.display = 'block';
        }

        // Load queue data for selected department
        function loadQueueData() {
            if (!currentDepartment) return;

            // Load current status
            fetch(`../api/queues/staff-status.php?dept_id=${currentDepartment}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateQueueDisplay(data);
                    }
                })
                .catch(error => console.error('Error loading queue data:', error));
        }

        // Update queue display
        function updateQueueDisplay(data) {
            // Update now serving
            document.getElementById('nowServing').textContent = data.now_serving || 'None';

            // Update next in line
            displayNextInLine(data.next_in_line);

            // Update currently serving
            displayCurrentlyServing(data.currently_serving);
        }

        // Display next in line
        function displayNextInLine(queueList) {
            const container = document.getElementById('nextInLine');
            
            if (!queueList || queueList.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No one waiting</div>';
                return;
            }

            let html = '<div class="row g-2">';
            queueList.slice(0, 5).forEach((queue, index) => {
                html += `
                    <div class="col-md-6">
                        <div class="card border-light">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${queue.ticket_no}</h6>
                                        <small class="text-muted">${queue.customer_name}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">${queue.wait_time} min</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        // Display currently serving
        function displayCurrentlyServing(queueList) {
            const container = document.getElementById('currentlyServing');
            
            if (!queueList || queueList.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No one currently being served</div>';
                return;
            }

            let html = '<div class="row g-3">';
            queueList.forEach(queue => {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-warning">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">${queue.ticket_no}</h6>
                                        <small class="text-muted">${queue.customer_name}</small>
                                    </div>
                                    <span class="badge bg-warning">Serving</span>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-sm btn-warning" onclick="skipCustomer(${queue.queue_id}, '${queue.customer_name}')">
                                        <i class="bi bi-skip-forward"></i> Skip
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="markDone(${queue.queue_id}, '${queue.customer_name}')">
                                        <i class="bi bi-check-circle"></i> Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        // Call next customer
        function callNext() {
            if (!currentDepartment) return;

            const formData = new FormData();
            formData.append('dept_id', currentDepartment);

            fetch('../api/queues/call-next.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Next customer called successfully!', 'success');
                    loadQueueData();
                } else {
                    showToast(data.message || 'Failed to call next customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error calling next customer:', error);
                showToast('Error calling next customer', 'error');
            });
        }

        // Skip customer
        function skipCustomer(queueId, customerName) {
            skipQueueId = queueId;
            document.getElementById('skipCustomerName').textContent = customerName;
            new bootstrap.Modal(document.getElementById('skipModal')).show();
        }

        // Confirm skip
        document.getElementById('confirmSkipBtn').addEventListener('click', function() {
            if (!skipQueueId) return;

            const formData = new FormData();
            formData.append('queue_id', skipQueueId);

            fetch('../api/queues/skip.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Customer skipped successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('skipModal')).hide();
                    loadQueueData();
                } else {
                    showToast(data.message || 'Failed to skip customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error skipping customer:', error);
                showToast('Error skipping customer', 'error');
            });
        });

        // Mark customer as done
        function markDone(queueId, customerName) {
            doneQueueId = queueId;
            document.getElementById('doneCustomerName').textContent = customerName;
            new bootstrap.Modal(document.getElementById('doneModal')).show();
        }

        // Confirm done
        document.getElementById('confirmDoneBtn').addEventListener('click', function() {
            if (!doneQueueId) return;

            const formData = new FormData();
            formData.append('queue_id', doneQueueId);

            fetch('../api/queues/done.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Customer marked as done successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('doneModal')).hide();
                    loadQueueData();
                } else {
                    showToast(data.message || 'Failed to mark customer as done', 'error');
                }
            })
            .catch(error => {
                console.error('Error marking customer as done:', error);
                showToast('Error marking customer as done', 'error');
            });
        });

        // Start real-time updates
        function startUpdates() {
            updateInterval = setInterval(() => {
                loadQueueData();
            }, 10000); // Update every 10 seconds
        }

        // Stop real-time updates
        function stopUpdates() {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopUpdates();
        });
    </script>
</body>
</html>
