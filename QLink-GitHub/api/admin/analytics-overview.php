<?php
require_once '../../includes/config.php';
require_once '../../includes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();

    $totalUsers = (int)$db->fetchColumn('SELECT COUNT(*) FROM users');
    $activeStaff = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role='staff' AND is_active=1");
    $activeStudents = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1");
    $totalDepartments = $db->tableExists('departments') ? (int)$db->fetchColumn('SELECT COUNT(*) FROM departments') : 0;
    $todayQueues = $db->tableExists('queues') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE DATE(created_at)=CURDATE()") : 0;
    $todayWaiting = $db->tableExists('queues') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='waiting' AND DATE(created_at)=CURDATE()") : 0;
    $todayServing = $db->tableExists('queues') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='serving' AND DATE(created_at)=CURDATE()") : 0;
    $todayDone = $db->tableExists('queues') ? (int)$db->fetchColumn("SELECT COUNT(*) FROM queues WHERE status='done' AND DATE(created_at)=CURDATE()") : 0;

    // Last 7 days queue trends (including days with zero)
    $trendLabels = [];
    $trendCounts = [];
    if ($db->tableExists('queues')) {
        // Fetch counts grouped by date for the last 7 days
        $trendRows = $db->fetchAll(
            "SELECT DATE(created_at) as d, COUNT(*) as cnt
             FROM queues
             WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(created_at)
             ORDER BY d ASC"
        );
        $byDate = [];
        foreach ($trendRows as $row) {
            $byDate[$row['d']] = (int)$row['cnt'];
        }
        for ($i = 6; $i >= 0; $i--) {
            $date = new DateTime();
            $date->modify('-' . $i . ' day');
            $key = $date->format('Y-m-d');
            $trendLabels[] = $date->format('M j');
            $trendCounts[] = isset($byDate[$key]) ? (int)$byDate[$key] : 0;
        }
    } else {
        // Fallback zeros if queues table missing
        for ($i = 6; $i >= 0; $i--) {
            $date = new DateTime();
            $date->modify('-' . $i . ' day');
            $trendLabels[] = $date->format('M j');
            $trendCounts[] = 0;
        }
    }

    // Top departments by queues today
    $topDepts = $db->tableExists('queues') ? $db->fetchAll(
        "SELECT d.name, COUNT(*) as cnt
         FROM queues q JOIN departments d ON d.dept_id=q.dept_id
         WHERE DATE(q.created_at)=CURDATE()
         GROUP BY d.dept_id, d.name
         ORDER BY cnt DESC
         LIMIT 5"
    ) : [];

    echo json_encode([
        'success' => true,
        'overview' => [
            'total_users' => $totalUsers,
            'active_staff' => $activeStaff,
            'active_students' => $activeStudents,
            'total_departments' => $totalDepartments,
            'today_queues' => $todayQueues,
            'today_waiting' => $todayWaiting,
            'today_serving' => $todayServing,
            'today_done' => $todayDone,
            'top_departments' => $topDepts,
            'queue_trends' => [
                'labels' => $trendLabels,
                'counts' => $trendCounts
            ]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
