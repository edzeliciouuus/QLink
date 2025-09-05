<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    if (!$db->tableExists('departments')) {
        echo json_encode(['success' => true, 'departments' => []]);
        exit();
    }

    $rows = $db->fetchAll(
        'SELECT dept_id, name, is_active FROM departments ORDER BY name ASC'
    );

    echo json_encode([
        'success' => true,
        'departments' => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
