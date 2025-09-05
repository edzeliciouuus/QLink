<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QLink - Smart Queuing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-link-45deg"></i> QLink
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-primary mb-4">
                        Smart Queuing Made Simple
                    </h1>
                    <p class="lead mb-4">
                        Join queues, track your position, and get notified when it's your turn. 
                        No more waiting in line - QLink keeps you informed every step of the way.
                    </p>
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="register.php" class="btn btn-primary btn-lg px-4">
                            Get Started
                        </a>
                        <a href="login.php" class="btn btn-outline-primary btn-lg px-4">
                            Sign In
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="bg-white p-4 rounded shadow">
                        <i class="bi bi-phone display-1 text-primary"></i>
                        <h3 class="mt-3">Mobile First</h3>
                        <p class="text-muted">Access from any device, anywhere</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Real-Time Updates</h5>
                        <p class="card-text">See your position in line and estimated wait time with live updates.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-bell display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Smart Notifications</h5>
                        <p class="card-text">Get notified when you're next in line via app and SMS.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up display-4 text-primary mb-3"></i>
                        <h5 class="card-title">Analytics & Insights</h5>
                        <p class="card-text">Track wait times, peak hours, and queue performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 QLink. Smart Queuing System for Schools.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
