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
    <title>Staff Management - Admin - QLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" href="staff.php"><i class="bi bi-people"></i> Staff</a></li>
                    <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
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
                <h1 class="h3 mb-0">Staff Management</h1>
                <p class="text-muted">Add, edit, and manage staff accounts</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people"></i> Staff List</h5>
                <a href="../register.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-plus"></i> Add Staff</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="staffTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadStaff();
        });

        function loadStaff() {
            fetch('../api/admin/staff-list.php')
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#staffTable tbody');
                    if (!data.success || !data.users || data.users.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No staff found</td></tr>';
                        return;
                    }
                    let html = '';
                    data.users.forEach(u => {
                        const status = parseInt(u.is_active) === 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                        html += `
                            <tr>
                                <td>${escapeHtml(u.name)}</td>
                                <td>${escapeHtml(u.email)}</td>
                                <td>${escapeHtml(u.phone || '')}</td>
                                <td><span class="badge ${u.role === 'admin' ? 'bg-dark' : 'bg-success'}">${u.role}</span></td>
                                <td>${status}</td>
                                <td><small class="text-muted">${u.created_at || ''}</small></td>
                                <td><small class="text-muted">${u.last_login || '-'}</small></td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    const tbody = document.querySelector('#staffTable tbody');
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load staff</td></tr>';
                });
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"] /g, function(s) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',' ':' '})[s];
            });
        }
    </script>
</body>
</html>
