<?php
require_once 'includes/config.php';
require_once 'includes/Auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$auth = new Auth();
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-link-45deg"></i> QLink
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger" id="notificationCount">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" id="notificationPanel">
                            <div class="dropdown-header">Notifications</div>
                            <div id="notificationList">
                                <div class="dropdown-item text-muted">No notifications</div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="api/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p class="text-muted">Manage your queue status and get real-time updates</p>
            </div>
        </div>

        <!-- Current Queue Status -->
        <div class="row mb-4" id="currentQueueSection" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                    <i class="bi bi-ticket-perforated"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Your Ticket</div>
                                    <div class="h4 mb-0" id="ticketNumber">-</div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-outline-danger btn-sm" id="cancelQueueBtn">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                            </div>
                        </div>

                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="text-muted small">Position</div>
                                <div class="h5 mb-0" id="position">-</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Now Serving</div>
                                <div class="h5 mb-0" id="nowServing">-</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Est. Wait</div>
                                <div class="h5 mb-0" id="eta">-</div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Queue Progress</span>
                                <span id="progressText">- %</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div id="queueProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join Queue Section -->
        <div class="row mb-4" id="joinQueueSection">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Join a Queue</h5>
                    </div>
                    <div class="card-body">
                        <form id="joinQueueForm">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="department" class="form-label">Select Department</label>
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="">Choose a department...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Join Queue
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Department Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="departmentStatus">
                            <div class="text-center text-muted">
                                <i class="bi bi-arrow-clockwise"></i> Loading department status...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="notificationToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="bi bi-bell text-primary me-2"></i>
                <strong class="me-auto">QLink Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                <!-- Toast message will be inserted here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Global variables
        let currentQueue = null;
        let updateInterval = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartments();
            loadDepartmentStatus();
            checkCurrentQueue();
            startStatusUpdates();
            loadNotifications();
        });

        // Load available departments
        function loadDepartments() {
            fetch('api/queues/departments.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('department');
                        select.innerHTML = '<option value="">Choose a department...</option>';
                        data.departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.dept_id;
                            option.textContent = dept.name;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading departments:', error));
        }

        // Check if user has an active queue
        function checkCurrentQueue() {
            fetch('api/queues/status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.queue) {
                        currentQueue = data.queue;
                        showCurrentQueue();
                    } else {
                        hideCurrentQueue();
                    }
                })
                .catch(error => console.error('Error checking queue status:', error));
        }

        // Show current queue section
        function showCurrentQueue() {
            document.getElementById('currentQueueSection').style.display = 'block';
            document.getElementById('joinQueueSection').style.display = 'none';
            updateQueueDisplay();
        }

        // Hide current queue section
        function hideCurrentQueue() {
            document.getElementById('currentQueueSection').style.display = 'none';
            document.getElementById('joinQueueSection').style.display = 'block';
            currentQueue = null;
        }

        // Update queue display
        function updateQueueDisplay() {
            if (!currentQueue) return;
            
            document.getElementById('ticketNumber').textContent = currentQueue.ticket_no;
            document.getElementById('position').textContent = currentQueue.position;
            document.getElementById('nowServing').textContent = currentQueue.now_serving;
            document.getElementById('eta').textContent = currentQueue.eta + ' min';

            // Progress: based on now_serving vs ticket
            const totalAhead = Math.max(0, parseInt(currentQueue.ticket_no, 10) - parseInt(currentQueue.now_serving || 0, 10));
            const progressed = Math.max(0, totalAhead - parseInt(currentQueue.position || 0, 10));
            const pct = totalAhead > 0 ? Math.min(100, Math.round((progressed / totalAhead) * 100)) : 100;
            const bar = document.getElementById('queueProgress');
            const txt = document.getElementById('progressText');
            if (bar) bar.style.width = pct + '%';
            if (txt) txt.textContent = pct + '%';
        }

        // Join queue form submission
        document.getElementById('joinQueueForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const department = document.getElementById('department').value;
            if (!department) {
                alert('Please select a department');
                return;
            }

            const formData = new FormData();
            formData.append('dept_id', department);

            fetch('api/queues/join.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Successfully joined queue!', 'success');
                    checkCurrentQueue();
                } else {
                    showToast(data.message || 'Failed to join queue', 'error');
                }
            })
            .catch(error => {
                console.error('Error joining queue:', error);
                showToast('Error joining queue', 'error');
            });
        });

        // Cancel queue
        document.getElementById('cancelQueueBtn').addEventListener('click', function() {
            if (!currentQueue) return;
            
            if (confirm('Are you sure you want to cancel your queue position?')) {
                const formData = new FormData();
                formData.append('queue_id', currentQueue.queue_id);

                fetch('api/queues/cancel.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Queue cancelled successfully', 'success');
                        hideCurrentQueue();
                    } else {
                        showToast(data.message || 'Failed to cancel queue', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error cancelling queue:', error);
                    showToast('Error cancelling queue', 'error');
                });
            }
        });

        // Load department status
        function loadDepartmentStatus() {
            fetch('api/queues/department-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDepartmentStatus(data.departments);
                    }
                })
                .catch(error => console.error('Error loading department status:', error));
        }

        // Display department status
        function displayDepartmentStatus(departments) {
            const container = document.getElementById('departmentStatus');
            
            if (departments.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No departments available</div>';
                return;
            }

            let html = '<div class="row g-3">';
            departments.forEach(dept => {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">${dept.name}</h6>
                                <p class="card-text">
                                    <strong>Now Serving:</strong> ${dept.now_serving || 'None'}<br>
                                    <strong>Waiting:</strong> ${dept.waiting_count} people<br>
                                    <strong>Status:</strong> 
                                    <span class="badge ${dept.is_active ? 'bg-success' : 'bg-secondary'}">
                                        ${dept.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        // Start real-time status updates
        function startStatusUpdates() {
            updateInterval = setInterval(() => {
                if (currentQueue) {
                    checkCurrentQueue();
                }
                loadDepartmentStatus();
            }, 10000); // Update every 10 seconds
        }

        // Load notifications
        function loadNotifications() {
            fetch('api/notifications/list.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotifications(data.notifications);
                        updateNotificationCount(data.unread_count);
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Display notifications
        function displayNotifications(notifications) {
            const container = document.getElementById('notificationList');
            
            if (notifications.length === 0) {
                container.innerHTML = '<div class="dropdown-item text-muted">No notifications</div>';
                return;
            }

            let html = '';
            notifications.forEach(notif => {
                const timeAgo = getTimeAgo(notif.sent_at);
                html += `
                    <a class="dropdown-item ${notif.sent_status === 'pending' ? 'fw-bold' : ''}" href="#">
                        <div class="d-flex w-100 justify-content-between">
                            <small>${notif.message}</small>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                    </a>
                `;
            });
            
            container.innerHTML = html;
        }

        // Update notification count
        function updateNotificationCount(count) {
            const badge = document.getElementById('notificationCount');
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            
            // Set toast color based on type
            toast.className = 'toast';
            if (type === 'success') toast.classList.add('border-success');
            if (type === 'error') toast.classList.add('border-danger');
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Get time ago
        function getTimeAgo(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diffMs = now - time;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            
            if (diffDays > 0) return `${diffDays}d ago`;
            if (diffHours > 0) return `${diffHours}h ago`;
            if (diffMins > 0) return `${diffMins}m ago`;
            return 'Just now';
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
</body>
</html>

