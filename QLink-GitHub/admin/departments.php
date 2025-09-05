<?php
require_once '../includes/config.php';
require_once '../includes/Auth.php';
require_once '../includes/csrf.php';

$auth = new Auth();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch departments
try {
    $db = Database::getInstance();
    $departments = $db->fetchAll('SELECT dept_id, name, code, description, is_active, created_at FROM departments ORDER BY created_at DESC');
} catch (Exception $e) {
    $departments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Admin - QLink</title>
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="departments.php">Departments</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff.php">Staff</a></li>
                    <li class="nav-item"><a class="nav-link" href="analytics.php">Analytics</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="../api/auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-building-add"></i> Add Department
                    </div>
                    <div class="card-body">
                        <form id="addDeptForm" method="POST">
                            <?php echo generateCSRFHiddenInput(); ?>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" name="code" maxlength="10" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-plus-circle"></i> Create Department
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-list-ul"></i> Departments
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($departments)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No departments yet</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($departments as $d): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($d['name']); ?></td>
                                                <td><span class="badge text-bg-dark"><?php echo htmlspecialchars($d['code']); ?></span></td>
                                                <td>
                                                    <?php if ((int)$d['is_active'] === 1): ?>
                                                        <span class="badge text-bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($d['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('addDeptForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            fetch('../api/admin/departments/create.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to create department.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred.');
            });
        });
    </script>
</body>
</html>
