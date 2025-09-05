<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$queueId = isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : 0;
if ($queueId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid queue']);
    exit();
}

try {
    $db = Database::getInstance();
    $db->beginTransaction();

    $q = $db->fetchOne('SELECT queue_id, dept_id FROM queues WHERE queue_id = :qid AND status = \'serving\'', ['qid' => $queueId]);
    if (!$q) {
        throw new Exception('Queue not currently serving');
    }

    $next = (int)$db->fetchColumn('SELECT COALESCE(MAX(ticket_no),0)+1 FROM queues WHERE dept_id = :id AND DATE(created_at) = CURDATE()', ['id' => $q['dept_id']]);

    $db->update('queues', ['status' => 'waiting', 'ticket_no' => $next, 'started_at' => null], 'queue_id = :qid', ['qid' => $queueId]);

    $db->commit();

    // Log activity
    $dept = $db->fetchOne('SELECT name FROM departments WHERE dept_id = :id', ['id' => $q['dept_id']]);
    logActivity('queue_skip', 'Skipped current and reassigned ticket at ' . ($dept['name'] ?? ('Department #' . $q['dept_id'])));

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db && $db->inTransaction()) { $db->rollback(); }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
