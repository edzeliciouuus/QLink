<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    if (!$db->tableExists('activity_log')) {
        echo json_encode(['success' => true, 'activities' => []]);
        exit();
    }

    // Show only queue-related activities, including legacy action names
    $rows = $db->fetchAll("SELECT user_id, action, description, created_at 
                           FROM activity_log 
                           WHERE action LIKE 'queue_%' 
                              OR action IN ('join_queue','call_next','mark_done','cancel_queue','missed_queue')
                           ORDER BY created_at DESC 
                           LIMIT 20");

    // If no rows found, synthesize from queues/queue_history (not limited to today) as a fallback
    if (!$rows || count($rows) === 0) {
        $synth = $db->fetchAll(
            "SELECT * FROM (
                SELECT q.user_id AS user_id,
                       'queue_join' AS action,
                       CONCAT('Joined ', d.name, ' (Ticket #', q.ticket_no, ')') AS description,
                       q.created_at AS created_at
                FROM queues q
                JOIN departments d ON d.dept_id = q.dept_id
                UNION ALL
                SELECT q.user_id AS user_id,
                       'queue_call_next' AS action,
                       CONCAT('Called next at ', d.name, ' (Ticket #', q.ticket_no, ')') AS description,
                       q.started_at AS created_at
                FROM queues q
                JOIN departments d ON d.dept_id = q.dept_id
                WHERE q.started_at IS NOT NULL
                UNION ALL
                SELECT h.user_id AS user_id,
                       CASE 
                           WHEN h.outcome = 'served' THEN 'queue_done'
                           WHEN h.outcome = 'cancelled' THEN 'queue_cancel'
                           WHEN h.outcome = 'missed' THEN 'queue_missed'
                       END AS action,
                       CONCAT('Ticket #', h.ticket_no, ' ', 
                              CASE 
                                  WHEN h.outcome = 'served' THEN 'served'
                                  WHEN h.outcome = 'cancelled' THEN 'cancelled'
                                  WHEN h.outcome = 'missed' THEN 'missed'
                              END,
                              ' at ', d.name) AS description,
                       COALESCE(h.archived_at, h.finished_at, h.created_at) AS created_at
                FROM queue_history h
                JOIN departments d ON d.dept_id = h.dept_id
            ) AS t
            WHERE t.created_at IS NOT NULL
            ORDER BY t.created_at DESC
            LIMIT 20"
        );
        $rows = $synth ?: [];
    }

    // Final fallback: derive from queues table only (no joins)
    if (!$rows || count($rows) === 0) {
        $fallback = $db->fetchAll(
            "SELECT * FROM (
                SELECT user_id,
                       CASE 
                           WHEN status = 'waiting' THEN 'queue_join'
                           WHEN status = 'serving' THEN 'queue_call_next'
                           WHEN status = 'done' THEN 'queue_done'
                           WHEN status = 'cancelled' THEN 'queue_cancel'
                           WHEN status = 'missed' THEN 'queue_missed'
                           ELSE 'queue_update'
                       END AS action,
                       CONCAT('Ticket #', ticket_no, ' ', status) AS description,
                       COALESCE(finished_at, started_at, created_at) AS created_at
                FROM queues
            ) t
            WHERE t.created_at IS NOT NULL
            ORDER BY t.created_at DESC
            LIMIT 20"
        );
        $rows = $fallback ?: [];
    }

    echo json_encode(['success' => true, 'activities' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
