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

    $q = $db->fetchOne('SELECT queue_id, dept_id, ticket_no FROM queues WHERE queue_id = :qid AND status = \'serving\'', ['qid' => $queueId]);
    if (!$q) {
        throw new Exception('Queue not currently serving');
    }

    $db->update('queues', ['status' => 'done', 'finished_at' => date('Y-m-d H:i:s')], 'queue_id = :qid', ['qid' => $queueId]);

    // Log activity
    $dept = $db->fetchOne('SELECT name FROM departments WHERE dept_id = :id', ['id' => $q['dept_id']]);
    logActivity('queue_done', 'Ticket #' . ($q['ticket_no'] ?? '') . ' served at ' . ($dept['name'] ?? ('Department #' . $q['dept_id'])));

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
