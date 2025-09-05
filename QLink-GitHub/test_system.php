<?php
/**
 * QLink System Test Script
 * Run this to verify all components are working
 */

echo "<h1>QLink System Test</h1>\n";
echo "<pre>\n";

// Test 1: PHP Version
echo "=== PHP Version ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP Extensions: " . implode(', ', get_loaded_extensions()) . "\n\n";

// Test 2: Configuration
echo "=== Configuration ===\n";
if (file_exists('includes/config.php')) {
    require_once 'includes/config.php';
    echo "✓ Config file loaded\n";
    echo "Database Host: " . DB_HOST . "\n";
    echo "Database Name: " . DB_NAME . "\n";
    echo "App Name: " . APP_NAME . "\n";
    echo "Debug Mode: " . (DEBUG_MODE ? 'ON' : 'OFF') . "\n\n";
} else {
    echo "✗ Config file not found\n\n";
}

// Test 3: Database Connection
echo "=== Database Connection ===\n";
if (class_exists('Database')) {
    try {
        $db = Database::getInstance();
        echo "✓ Database class loaded\n";
        
        // Test connection
        $result = $db->query("SELECT 1 as test");
        if ($result) {
            echo "✓ Database connection successful\n";
        } else {
            echo "✗ Database connection failed\n";
        }
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Database class not found\n";
}
echo "\n";

// Test 4: CSRF Protection
echo "=== CSRF Protection ===\n";
if (function_exists('generateCSRFToken')) {
    echo "✓ CSRF functions loaded\n";
    $token = generateCSRFToken();
    echo "CSRF Token: " . substr($token, 0, 20) . "...\n";
} else {
    echo "✗ CSRF functions not found\n";
}
echo "\n";

// Test 5: Authentication
echo "=== Authentication ===\n";
if (class_exists('Auth')) {
    echo "✓ Auth class loaded\n";
} else {
    echo "✗ Auth class not found\n";
}
echo "\n";

// Test 6: Session
echo "=== Session ===\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is active\n";
    echo "Session ID: " . session_id() . "\n";
} else {
    echo "✗ Session not active\n";
}
echo "\n";

// Test 7: File Permissions
echo "=== File Permissions ===\n";
$test_files = [
    'includes/config.php' => 'Config file',
    'includes/Database.php' => 'Database class',
    'includes/Auth.php' => 'Auth class',
    'includes/csrf.php' => 'CSRF file',
    'api/auth/register.php' => 'Register API',
    'api/auth/login.php' => 'Login API',
    'api/auth/logout.php' => 'Logout API'
];

foreach ($test_files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description: $file\n";
    } else {
        echo "✗ $description: $file (missing)\n";
    }
}
echo "\n";

// Test 8: Database Schema
echo "=== Database Schema ===\n";
if (class_exists('Database')) {
    try {
        $db = Database::getInstance();
        
        // Check if tables exist
        $tables = ['users', 'departments', 'queues', 'dept_now_serving'];
        foreach ($tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->rowCount() > 0) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "✗ Table '$table' missing\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Cannot check database schema: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Database class not available\n";
}

echo "\n=== Test Complete ===\n";
echo "</pre>\n";

// Show next steps
echo "<h2>Next Steps:</h2>\n";
echo "<ol>\n";
echo "<li>Start MySQL in XAMPP Control Panel</li>\n";
echo "<li>Import database/qlink.sql into MySQL</li>\n";
echo "<li>Test registration at register.php</li>\n";
echo "<li>Test login at login.php</li>\n";
echo "</ol>\n";
?>
