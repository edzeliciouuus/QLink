<?php
// Sample configuration file for QLink
// Copy this file to config.php and update with your actual values

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'qlink');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// SMS Configuration (Semaphore API)
define('SEMAPHORE_API_KEY', 'your_semaphore_api_key_here');

// Security Settings
define('SESSION_LIFETIME', 3600);        // Session timeout in seconds (1 hour)
define('CSRF_TOKEN_LIFETIME', 1800);     // CSRF token timeout in seconds (30 minutes)
define('PASSWORD_MIN_LENGTH', 8);        // Minimum password length

// Debug Mode (set to false in production)
define('DEBUG_MODE', false);

// Application Settings
define('APP_NAME', 'QLink');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/QLink/');

// File Upload Settings
define('MAX_FILE_SIZE', 5242880);        // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Email Settings (if using email notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('FROM_EMAIL', 'noreply@qlink.edu.ph');
define('FROM_NAME', 'QLink System');

// Queue Settings
define('MAX_QUEUE_LENGTH', 100);         // Maximum number of people in a queue
define('QUEUE_TIMEOUT', 3600);           // Queue timeout in seconds (1 hour)
define('NOTIFICATION_INTERVAL', 300);    // Notification interval in seconds (5 minutes)

// System Settings
define('TIMEZONE', 'Asia/Manila');       // Set your timezone
define('DATE_FORMAT', 'Y-m-d H:i:s');    // Date format for display
define('CURRENCY', 'PHP');               // Currency symbol

// API Settings
define('API_RATE_LIMIT', 100);           // API requests per hour per IP
define('API_TIMEOUT', 30);               // API timeout in seconds

// Logging Settings
define('LOG_LEVEL', 'INFO');             // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE', 'logs/app.log');      // Log file path

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600);          // Cache lifetime in seconds

// Backup Settings
define('BACKUP_ENABLED', true);
define('BACKUP_INTERVAL', 86400);        // Backup interval in seconds (24 hours)
define('BACKUP_RETENTION', 7);           // Number of backups to keep

// Security Headers
define('ENABLE_SECURITY_HEADERS', true);
define('ENABLE_HTTPS_REDIRECT', false);  // Set to true in production

// Performance Settings
define('ENABLE_COMPRESSION', true);
define('ENABLE_CACHING', true);
define('CACHE_DRIVER', 'file');          // file, redis, memcached

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set(TIMEZONE);

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Include required files
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/csrf.php';

// Initialize database connection
try {
    $db = new Database();
} catch (Exception $e) {
    if (DEBUG_MODE) {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Database connection failed. Please check your configuration.');
    }
}
?>
