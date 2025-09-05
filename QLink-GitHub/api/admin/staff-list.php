<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    $rows = $db->fetchAll(
        "SELECT user_id, name, email, phone, role, is_active, created_at, last_login
         FROM users
         WHERE role IN ('staff', 'admin')
         ORDER BY role DESC, created_at DESC"
    );

    echo json_encode(['success' => true, 'users' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
