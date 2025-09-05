<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'queue' => null]);
    exit();
}

try {
    $db = Database::getInstance();

    // Get the current user's active queue for today
    $row = $db->fetchOne(
        "SELECT q.queue_id, q.ticket_no, q.dept_id, q.status, q.created_at
         FROM queues q
         WHERE q.user_id = :uid
           AND DATE(q.created_at) = CURDATE()
           AND q.status IN ('waiting','serving')
         ORDER BY q.created_at DESC
         LIMIT 1",
        ['uid' => $_SESSION['user_id']]
    );

    if (!$row) {
        echo json_encode(['success' => true, 'queue' => null]);
        exit();
    }

    // Compute now serving and position within the same department for today
    $nowServing = (int)$db->fetchColumn(
        "SELECT COALESCE(MAX(ticket_no), 0)
         FROM queues
         WHERE dept_id = :dept
           AND status IN ('serving','done')
           AND DATE(created_at) = CURDATE()",
        ['dept' => $row['dept_id']]
    );

    $position = (int)$db->fetchColumn(
        "SELECT COUNT(*)
         FROM queues
         WHERE dept_id = :dept
           AND status = 'waiting'
           AND DATE(created_at) = CURDATE()
           AND ticket_no < :ticket",
        ['dept' => $row['dept_id'], 'ticket' => $row['ticket_no']]
    );

    $row['now_serving'] = $nowServing;
    $row['position'] = $position;
    $row['eta'] = max(0, $position) * 2; // simple ETA: 2 mins per person

    echo json_encode(['success' => true, 'queue' => $row]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
