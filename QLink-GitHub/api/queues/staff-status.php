<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

$deptId = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : 0;
if ($deptId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit();
}

try {
    $db = Database::getInstance();

    // Now serving (0 if table/row missing)
    $nowServing = 0;
    if ($db->tableExists('dept_now_serving')) {
        $nowServing = (int)$db->fetchColumn('SELECT now_serving FROM dept_now_serving WHERE dept_id = :id', ['id' => $deptId]);
    }

    // Next in line (up to 10)
    $nextInLine = $db->fetchAll(
        "SELECT q.queue_id, q.ticket_no, u.name AS customer_name,
                TIMESTAMPDIFF(MINUTE, q.created_at, NOW()) AS wait_time
         FROM queues q
         JOIN users u ON u.user_id = q.user_id
         WHERE q.dept_id = :id AND q.status = 'waiting' AND DATE(q.created_at) = CURDATE()
         ORDER BY q.ticket_no ASC
         LIMIT 10",
        ['id' => $deptId]
    );

    // Currently serving
    $currentlyServing = $db->fetchAll(
        "SELECT q.queue_id, q.ticket_no, u.name AS customer_name
         FROM queues q
         JOIN users u ON u.user_id = q.user_id
         WHERE q.dept_id = :id AND q.status = 'serving' AND DATE(q.created_at) = CURDATE()
         ORDER BY q.started_at DESC",
        ['id' => $deptId]
    );

    echo json_encode([
        'success' => true,
        'now_serving' => $nowServing,
        'next_in_line' => $nextInLine,
        'currently_serving' => $currentlyServing,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
