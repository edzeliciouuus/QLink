<?php
require_once '../includes/config.php';
require_once '../includes/Auth.php';

// Require admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
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
    <title>Analytics - Admin - QLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="departments.php"><i class="bi bi-building"></i> Departments</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff.php"><i class="bi bi-people"></i> Staff</a></li>
                    <li class="nav-item"><a class="nav-link active" href="analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
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
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">Analytics</h1>
                <p class="text-muted">System metrics and insights</p>
            </div>
        </div>

        <div id="overviewCards" class="row g-3 mb-4">
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Total Users</div><div class="h4" id="a_total_users">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Active Staff</div><div class="h4" id="a_active_staff">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Active Students</div><div class="h4" id="a_active_students">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Departments</div><div class="h4" id="a_total_departments">-</div></div></div></div>
        </div>

        <div id="todayCards" class="row g-3 mb-4">
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Queues Today</div><div class="h4" id="a_today_queues">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Waiting</div><div class="h4" id="a_today_waiting">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Serving</div><div class="h4" id="a_today_serving">-</div></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body text-center"><div class="text-muted small">Done</div><div class="h4" id="a_today_done">-</div></div></div></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Queue Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="queueStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Top Departments Today</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="topDeptsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Queue Trends (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="queueTrendsChart" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people"></i> User Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userDistributionChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let charts = {};

        document.addEventListener('DOMContentLoaded', function() {
            loadOverview();
        });

        function loadOverview() {
            fetch('../api/admin/analytics-overview.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const o = data.overview || {};
                    setText('a_total_users', o.total_users);
                    setText('a_active_staff', o.active_staff);
                    setText('a_active_students', o.active_students);
                    setText('a_total_departments', o.total_departments);
                    setText('a_today_queues', o.today_queues);
                    setText('a_today_waiting', o.today_waiting);
                    setText('a_today_serving', o.today_serving);
                    setText('a_today_done', o.today_done);
                    renderTopDepts(o.top_departments || []);
                    renderCharts(o);
                })
                .catch(err => console.error(err));
        }

        function setText(id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        function renderTopDepts(rows) {
            // No-op: table removed for redundancy. Keep function to avoid errors.
            return;
        }

        function renderCharts(data) {
            // Queue Status Pie Chart
            const queueStatusCtx = document.getElementById('queueStatusChart').getContext('2d');
            if (charts.queueStatus) charts.queueStatus.destroy();
            charts.queueStatus = new Chart(queueStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Waiting', 'Serving', 'Done'],
                    datasets: [{
                        data: [data.today_waiting || 0, data.today_serving || 0, data.today_done || 0],
                        backgroundColor: ['#ffc107', '#17a2b8', '#28a745'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Top Departments Bar Chart
            const topDeptsCtx = document.getElementById('topDeptsChart').getContext('2d');
            if (charts.topDepts) charts.topDepts.destroy();
            const deptNames = (data.top_departments || []).map(d => d.name);
            const deptCounts = (data.top_departments || []).map(d => d.cnt);
            charts.topDepts = new Chart(topDeptsCtx, {
                type: 'bar',
                data: {
                    labels: deptNames,
                    datasets: [{
                        label: 'Queues Today',
                        data: deptCounts,
                        backgroundColor: '#007bff',
                        borderColor: '#0056b3',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : null;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const v = ctx.parsed.y;
                                    return (ctx.dataset.label ? ctx.dataset.label + ': ' : '') + Math.round(v);
                                }
                            }
                        }
                    }
                }
            });

            // User Distribution Pie Chart
            const userDistCtx = document.getElementById('userDistributionChart').getContext('2d');
            if (charts.userDist) charts.userDist.destroy();
            charts.userDist = new Chart(userDistCtx, {
                type: 'pie',
                data: {
                    labels: ['Staff', 'Students', 'Admins'],
                    datasets: [{
                        data: [data.active_staff || 0, data.active_students || 0, 1],
                        backgroundColor: ['#17a2b8', '#28a745', '#6c757d'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Queue Trends Line Chart (real data from API)
            const trendsCtx = document.getElementById('queueTrendsChart').getContext('2d');
            if (charts.trends) charts.trends.destroy();
            const last7Days = (data.queue_trends && data.queue_trends.labels) ? data.queue_trends.labels : [];
            const trendData = (data.queue_trends && data.queue_trends.counts) ? data.queue_trends.counts : [];
            charts.trends = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: last7Days,
                    datasets: [{
                        label: 'Total Queues',
                        data: trendData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
        }
    </script>
</body>
</html>
