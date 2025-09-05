<?php
require_once '../../../includes/config.php';
require_once '../../../includes/Database.php';
require_once '../../../includes/csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!verifyCSRFTokenFromPost()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired security token']);
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || $code === '') {
        throw new Exception('Name and code are required');
    }

    if (!preg_match('/^[A-Z0-9_-]{2,10}$/', $code)) {
        throw new Exception('Code must be 2-10 characters (A-Z, 0-9, _ or -)');
    }

    $db = Database::getInstance();

    if (!$db->tableExists('departments')) {
        throw new Exception('Departments table not found. Import database/qlink.sql');
    }

    // Ensure 'code' column exists and is unique
    $cols = $db->getTableColumns('departments');
    if (!in_array('code', $cols, true)) {
        // Add column as NULLable first to allow backfill
        $db->executeRaw("ALTER TABLE departments ADD COLUMN code VARCHAR(20) NULL AFTER name");
        // Backfill codes based on name + dept_id to ensure uniqueness
        $db->executeRaw("UPDATE departments SET code = CONCAT(UPPER(REPLACE(LEFT(name,10),' ' ,'_')),'_',dept_id) WHERE code IS NULL OR code = ''");
        // Add unique index
        $db->executeRaw("ALTER TABLE departments ADD UNIQUE KEY uniq_code (code)");
        // Enforce NOT NULL
        $db->executeRaw("ALTER TABLE departments MODIFY code VARCHAR(20) NOT NULL");
    }

    // Uniqueness check
    if ($db->exists('departments', 'code = :code', ['code' => $code])) {
        throw new Exception('Department code already exists');
    }

    $deptId = $db->insert('departments', [
        'name' => $name,
        'code' => $code,
        'description' => $description,
        'is_active' => $is_active,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    if (!$deptId) {
        throw new Exception('Failed to create department');
    }

    echo json_encode(['success' => true, 'dept_id' => $deptId]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
