<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'status' => [
        'sms_working' => true,
        'last_backup' => null
    ]
]);
