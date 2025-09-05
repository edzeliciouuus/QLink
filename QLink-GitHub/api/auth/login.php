<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';
require_once '../../includes/Auth.php';
require_once '../../includes/csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!verifyCSRFTokenFromPost()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired security token']);
    exit();
}

try {
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $password = $_POST['password'] ?? '';

    $auth = new Auth();
    $result = $auth->login($email, $password);

    if ($result['success']) {
        $role = $_SESSION['role'] ?? ($result['user']['role'] ?? 'student');
        $redirect = 'dashboard.php';
        if ($role === 'admin') { $redirect = 'admin/'; }
        if ($role === 'staff') { $redirect = 'staff/'; }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => $redirect,
        ]);
        exit();
    }

    throw new Exception($result['message'] ?? 'Login failed. Please try again.');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
?>
