<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    $totalUsers = (int)$db->fetchColumn('SELECT COUNT(*) FROM users');
    $totalDepartments = $db->tableExists('departments') ? (int)$db->fetchColumn('SELECT COUNT(*) FROM departments') : 0;
    $activeQueues = $db->tableExists('queues') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status IN ('waiting','serving') AND DATE(created_at) = CURDATE()") : 0;
    // Placeholder for SMS (no real integration yet): count notifications sent today
    $smsSentToday = $db->tableExists('notifications') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM notifications WHERE channel = 'sms' AND DATE(sent_at) = CURDATE() AND sent_status = 'sent'") : 0;

    // Today's KPI metrics
    $avgWait = 0; $avgService = 0; $done = 0; $cancelled = 0; $missed = 0; $throughput = 0;
    if ($db->tableExists('queues')) {
        // Avg wait time: from created_at to started_at for served today
        $avgWait = (float)$db->fetchColumn(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, started_at))
             FROM queues
             WHERE started_at IS NOT NULL AND DATE(created_at) = CURDATE()"
        );
        // Avg service time: from started_at to finished_at for done today
        $avgService = (float)$db->fetchColumn(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, finished_at))
             FROM queues
             WHERE finished_at IS NOT NULL AND DATE(finished_at) = CURDATE()"
        );
        // Outcome counts today
        $done = (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='done' AND DATE(finished_at)=CURDATE()");
        $cancelled = (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='cancelled' AND DATE(created_at)=CURDATE()");
        $missed = (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='missed' AND DATE(created_at)=CURDATE()");
        $throughput = $done; // done per day
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'total_departments' => $totalDepartments,
            'active_queues' => $activeQueues,
            'sms_sent_today' => $smsSentToday,
            'avg_wait_minutes' => round($avgWait ?? 0),
            'avg_service_minutes' => round($avgService ?? 0),
            'done_today' => $done,
            'cancelled_today' => $cancelled,
            'missed_today' => $missed,
            'throughput_today' => $throughput
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
