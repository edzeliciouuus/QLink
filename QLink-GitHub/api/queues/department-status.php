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
        "SELECT d.dept_id, d.name, d.is_active,
                COALESCE(dns.now_serving, 0) AS now_serving,
                (
                  SELECT COUNT(*) FROM queues q
                  WHERE q.dept_id = d.dept_id AND q.status = 'waiting' AND DATE(q.created_at) = CURDATE()
                ) AS waiting_count
         FROM departments d
         LEFT JOIN dept_now_serving dns ON dns.dept_id = d.dept_id
         ORDER BY d.name ASC"
    );

    echo json_encode(['success' => true, 'departments' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
