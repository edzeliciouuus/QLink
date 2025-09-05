<?php
/**
 * QLink Configuration File
 * Database and API configuration settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'qlink');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Semaphore SMS API Configuration
define('SEMAPHORE_API_KEY', 'your_semaphore_api_key_here');
define('SEMAPHORE_API_URL', 'https://api.semaphore.co/api/v4/messages');

// Application Configuration
define('APP_NAME', 'QLink');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/QLink');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'qlink_session');

// Security Configuration
define('CSRF_TOKEN_LIFETIME', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 8);

// Queue Configuration
define('MAX_QUEUE_SIZE', 100);
define('NOTIFICATION_THRESHOLD', 10); // Notify when within 10 positions
define('QUEUE_TIMEOUT_MINUTES', 30); // Mark as missed after 30 minutes

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Set session configuration BEFORE starting session
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_path', '/'); // Ensure cookie is available across all paths
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to get base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $path;
}

// Helper function to get current URL
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $uri;
}

// Helper function to check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Helper function to get client IP
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to validate phone number (basic validation)
function isValidPhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if length is between 10 and 15 digits
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// Helper function to sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Helper function to format date
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

// Helper function to get time ago
function getTimeAgo($datetime) {
    $time = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->diff($time);
    
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

// Helper function to log activity (persist to database if available)
function logActivity($action, $description = '', $user_id = null) {
    if (!$user_id && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }

    $ip = getClientIP();
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Attempt to write to activity_log table if Database class is available
    try {
        if (class_exists('Database')) {
            $db = Database::getInstance();
            if ($db->tableExists('activity_log')) {
                $db->insert('activity_log', [
                    'user_id' => $user_id,
                    'action' => $action,
                    'description' => $description,
                    'ip_address' => $ip,
                    'user_agent' => $agent,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                return;
            }
        }
    } catch (Exception $e) {
        // fall through to error_log
    }

    // Fallback to error_log if DB not available
    $logMessage = date('Y-m-d H:i:s') . " - User: $user_id - Action: $action - $description";
    error_log($logMessage);
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper function to check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

// Helper function to redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION[$type] = $message;
    header('Location: ' . $url);
    exit();
}

// Helper function to get flash message
function getFlashMessage($type) {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
        return $message;
    }
    return null;
}

// Helper function to check if flash message exists
function hasFlashMessage($type) {
    return isset($_SESSION[$type]);
}
?>
