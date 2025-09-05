<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$deptId = isset($_POST['dept_id']) ? (int)$_POST['dept_id'] : 0;
if ($deptId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit();
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();

    // Get next waiting
    $next = $db->fetchOne(
        "SELECT queue_id, ticket_no FROM queues WHERE dept_id = :id AND status = 'waiting' AND DATE(created_at) = CURDATE() ORDER BY ticket_no ASC LIMIT 1",
        ['id' => $deptId]
    );

    if (!$next) {
        $db->rollback();
        throw new Exception('No customers waiting');
    }

    // Update to serving
    $db->update('queues', ['status' => 'serving', 'started_at' => date('Y-m-d H:i:s')], 'queue_id = :qid', ['qid' => $next['queue_id']]);

    // Ensure dept_now_serving table exists
    if (!$db->tableExists('dept_now_serving')) {
        $db->executeRaw("CREATE TABLE IF NOT EXISTS `dept_now_serving` (
            `dept_id` INT UNSIGNED PRIMARY KEY,
            `now_serving` INT NOT NULL DEFAULT 0,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`dept_id`) REFERENCES `departments`(`dept_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    // Ensure row exists
    if (!$db->fetchOne('SELECT dept_id FROM dept_now_serving WHERE dept_id = :id', ['id' => $deptId])) {
        $db->insert('dept_now_serving', ['dept_id' => $deptId, 'now_serving' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    $db->update('dept_now_serving', ['now_serving' => $next['ticket_no'], 'updated_at' => date('Y-m-d H:i:s')], 'dept_id = :id', ['id' => $deptId]);

    $db->commit();

    // Log activity
    $dept = $db->fetchOne('SELECT name FROM departments WHERE dept_id = :id', ['id' => $deptId]);
    logActivity('queue_call_next', 'Called next at ' . ($dept['name'] ?? ('Department #' . $deptId)) . ' (Ticket #' . $next['ticket_no'] . ')');

    echo json_encode(['success' => true, 'ticket_no' => $next['ticket_no']]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) { $db->rollback(); }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
