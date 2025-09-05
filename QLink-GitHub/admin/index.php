<?php
require_once '../includes/config.php';
require_once '../includes/Auth.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$auth = new Auth();
if ($_SESSION['role'] !== 'admin') {
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
    <title>Admin Panel - QLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-shield-check"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departments.php">
                            <i class="bi bi-building"></i> Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="staff.php">
                            <i class="bi bi-people"></i> Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
                <h1 class="h3 mb-0">Admin Dashboard</h1>
                <p class="text-muted">Monitor and manage QLink system</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-people display-4 text-primary mb-2"></i>
                        <h4 class="card-title" id="totalUsers">-</h4>
                        <p class="card-text text-muted">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-building display-4 text-success mb-2"></i>
                        <h4 class="card-title" id="totalDepartments">-</h4>
                        <p class="card-text text-muted">Departments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history display-4 text-warning mb-2"></i>
                        <h4 class="card-title" id="activeQueues">-</h4>
                        <p class="card-text text-muted">Active Queues</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-bell display-4 text-info mb-2"></i>
                        <h4 class="card-title" id="smsSentToday">-</h4>
                        <p class="card-text text-muted">SMS Sent Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="row g-4">
            <!-- Today's Queue KPIs (replaces Recent Activity) -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Today's Queue KPIs</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="kpiRow">
                            <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">Avg Wait</div><div class="h5" id="kpiAvgWait">-</div></div></div>
                            <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">Avg Service</div><div class="h5" id="kpiAvgService">-</div></div></div>
                            <div class="col-md-2"><div class="border rounded p-3 text-center"><div class="text-muted small">Done</div><div class="h5" id="kpiDone">-</div></div></div>
                            <div class="col-md-2"><div class="border rounded p-3 text-center"><div class="text-muted small">Cancelled</div><div class="h5" id="kpiCancelled">-</div></div></div>
                            <div class="col-md-2"><div class="border rounded p-3 text-center"><div class="text-muted small">Missed</div><div class="h5" id="kpiMissed">-</div></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="departments.php" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Add Department
                            </a>
                            <a href="staff.php" class="btn btn-outline-success">
                                <i class="bi bi-person-plus"></i> Add Staff Member
                            </a>
                            <a href="analytics.php" class="btn btn-outline-info">
                                <i class="bi bi-graph-up"></i> View Analytics
                            </a>
                            <button class="btn btn-outline-warning" onclick="refreshStats()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> System Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Database</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>SMS Service</span>
                            <span class="badge bg-success" id="smsStatus">Online</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Last Backup</span>
                            <small class="text-muted" id="lastBackup">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Status Overview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Department Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <div id="departmentOverview">
                            <div class="text-center text-muted">
                                <i class="bi bi-arrow-clockwise"></i> Loading department overview...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        // Initialize admin dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            loadKpis();
            loadDepartmentOverview();
            checkSystemStatus();
        });

        // Load dashboard statistics
        function loadDashboardStats() {
            fetch('../api/admin/stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalUsers').textContent = data.stats.total_users;
                        document.getElementById('totalDepartments').textContent = data.stats.total_departments;
                        document.getElementById('activeQueues').textContent = data.stats.active_queues;
                        document.getElementById('smsSentToday').textContent = data.stats.sms_sent_today;
                    }
                })
                .catch(error => console.error('Error loading stats:', error));
        }

        // Load KPIs
        function loadKpis() {
            fetch('../api/admin/stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const s = data.stats;
                        document.getElementById('kpiAvgWait').textContent = (s.avg_wait_minutes ?? 0) + ' min';
                        document.getElementById('kpiAvgService').textContent = (s.avg_service_minutes ?? 0) + ' min';
                        document.getElementById('kpiDone').textContent = s.done_today ?? 0;
                        document.getElementById('kpiCancelled').textContent = s.cancelled_today ?? 0;
                        document.getElementById('kpiMissed').textContent = s.missed_today ?? 0;
                    }
                })
                .catch(error => console.error('Error loading KPIs:', error));
        }

        // Display recent activity
        function displayRecentActivity(activities) {
            const container = document.getElementById('recentActivity');
            
            if (!activities || activities.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No recent activity</div>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            activities.forEach(activity => {
                const timeAgo = getTimeAgo(activity.created_at);
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">${activity.action}</div>
                            <small class="text-muted">${activity.description}</small>
                        </div>
                        <small class="text-muted">${timeAgo}</small>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        // Load department overview
        function loadDepartmentOverview() {
            fetch('../api/admin/department-overview.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDepartmentOverview(data.departments);
                    }
                })
                .catch(error => console.error('Error loading department overview:', error));
        }

        // Display department overview
        function displayDepartmentOverview(departments) {
            const container = document.getElementById('departmentOverview');
            
            if (!departments || departments.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No departments available</div>';
                return;
            }

            let html = '<div class="row g-3">';
            departments.forEach(dept => {
                const statusClass = dept.is_active ? 'success' : 'secondary';
                const statusText = dept.is_active ? 'Active' : 'Inactive';
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">${dept.name}</h6>
                                    <span class="badge bg-${statusClass}">${statusText}</span>
                                </div>
                                <p class="card-text small">
                                    <strong>Now Serving:</strong> ${dept.now_serving || 'None'}<br>
                                    <strong>Waiting:</strong> ${dept.waiting_count} people<br>
                                    <strong>Today's Queues:</strong> ${dept.today_queues}
                                </p>
                                <div class="d-grid">
                                    <a href="departments.php?edit=${dept.dept_id}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        // Check system status
        function checkSystemStatus() {
            // Check SMS service status
            fetch('../api/admin/system-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const smsStatus = document.getElementById('smsStatus');
                        if (data.status.sms_working) {
                            smsStatus.className = 'badge bg-success';
                            smsStatus.textContent = 'Online';
                        } else {
                            smsStatus.className = 'badge bg-danger';
                            smsStatus.textContent = 'Offline';
                        }
                        
                        document.getElementById('lastBackup').textContent = data.status.last_backup || 'Never';
                    }
                })
                .catch(error => {
                    console.error('Error checking system status:', error);
                    document.getElementById('smsStatus').className = 'badge bg-warning';
                    document.getElementById('smsStatus').textContent = 'Unknown';
                });
        }

        // Refresh statistics
        function refreshStats() {
            loadDashboardStats();
            loadKpis();
            loadDepartmentOverview();
            checkSystemStatus();
            
            // Show refresh feedback
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Refreshed!';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
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
    </script>
</body>
</html>
