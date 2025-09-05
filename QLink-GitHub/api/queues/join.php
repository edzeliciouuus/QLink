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

    $deptId = isset($_POST['dept_id']) ? (int)$_POST['dept_id'] : 0;
    if ($deptId <= 0) {
        throw new Exception('Invalid department');
    }

    if (!$db->tableExists('queues') || !$db->tableExists('departments')) {
        throw new Exception('Database not initialized. Import database/qlink.sql');
    }

    // Ensure department is active
    $dept = $db->fetchOne('SELECT dept_id, name FROM departments WHERE dept_id = :id AND is_active = 1', ['id' => $deptId]);
    if (!$dept) {
        throw new Exception('Department not found or inactive');
    }

    $userId = (int)$_SESSION['user_id'];

    // Check if user already in queue today for this department (waiting/serving)
    $already = $db->fetchColumn(
        "SELECT COUNT(*) FROM queues WHERE user_id = :uid AND dept_id = :did AND status IN ('waiting','serving') AND DATE(created_at) = CURDATE()",
        ['uid' => $userId, 'did' => $deptId]
    );
    if ($already > 0) {
        throw new Exception('You are already queued for this department today');
    }

    // Next ticket number for today
    $next = (int)$db->fetchColumn(
        'SELECT COALESCE(MAX(ticket_no),0)+1 FROM queues WHERE dept_id = :did AND DATE(created_at) = CURDATE()',
        ['did' => $deptId]
    );

    $queueId = $db->insert('queues', [
        'user_id' => $userId,
        'dept_id' => $deptId,
        'ticket_no' => $next,
        'status' => 'waiting',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Log activity
    logActivity('queue_join', 'Joined ' . ($dept['name'] ?? ('Department #' . $deptId)) . ' (Ticket #' . $next . ')', $userId);

    echo json_encode(['success' => true, 'queue_id' => $queueId, 'ticket_no' => $next]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
