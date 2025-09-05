<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in']);
    exit();
}

try {
    $db = Database::getInstance();

    $queueId = isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : 0;
    if ($queueId <= 0) {
        throw new Exception('Invalid queue');
    }

    // Ensure the queue belongs to the user and is active today
    $q = $db->fetchOne('SELECT queue_id FROM queues WHERE queue_id = :qid AND user_id = :uid AND status IN (\'waiting\', \'serving\') AND DATE(created_at) = CURDATE()', ['qid' => $queueId, 'uid' => $_SESSION['user_id']]);
    if (!$q) {
        throw new Exception('Queue not found or not cancellable');
    }

    $db->update('queues', ['status' => 'cancelled'], 'queue_id = :qid', ['qid' => $queueId]);

    // Log activity
    $qInfo = $db->fetchOne('SELECT dept_id, ticket_no FROM queues WHERE queue_id = :qid', ['qid' => $queueId]);
    $dept = $qInfo ? $db->fetchOne('SELECT name FROM departments WHERE dept_id = :id', ['id' => $qInfo['dept_id']]) : null;
    logActivity('queue_cancel', 'Cancelled ' . (($qInfo['ticket_no'] ?? null) ? ('Ticket #' . $qInfo['ticket_no'] . ' ') : '') . 'at ' . (($dept['name'] ?? null) ? $dept['name'] : 'department'));

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
