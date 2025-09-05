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
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        throw new Exception('Passwords do not match');
    }

    $data = [
        'first_name' => sanitizeInput($_POST['first_name']),
        'last_name'  => sanitizeInput($_POST['last_name']),
        'email'      => sanitizeInput($_POST['email']),
        'phone'      => sanitizeInput($_POST['phone']),
        'role'       => 'student',
        'password'   => $_POST['password'],
    ];

    $auth = new Auth();
    $result = $auth->register($data);

    if ($result['success']) {
        $_SESSION['success'] = 'Account created successfully! Please sign in.';
        echo json_encode([
            'success' => true,
            'message' => $result['message'] ?? 'Registration successful!',
            'redirect' => 'login.php'
        ]);
        exit();
    }

    throw new Exception($result['message'] ?? 'Registration failed. Please try again.');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
?>
