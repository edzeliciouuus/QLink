<?php
/**
 * CSRF Protection Class
 * Handles CSRF token generation, verification, and form protection
 */

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token has expired
    if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    // Verify token
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    return true;
}

/**
 * Generate hidden input field for CSRF token
 */
function generateCSRFHiddenInput() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from POST data
 */
function verifyCSRFTokenFromPost() {
    if (!isset($_POST['csrf_token'])) {
        return false;
    }
    
    return verifyCSRFToken($_POST['csrf_token']);
}

/**
 * Verify CSRF token from request data
 */
function verifyCSRFTokenFromRequest($token) {
    return verifyCSRFToken($token);
}

/**
 * Require CSRF token verification (redirect if invalid)
 */
function requireCSRFToken() {
    if (!verifyCSRFTokenFromPost()) {
        $_SESSION['error'] = 'Invalid or expired security token. Please try again.';
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/');
        exit();
    }
}

/**
 * Clean up expired CSRF tokens
 */
function cleanupExpiredCSRFTokens() {
    if (isset($_SESSION['csrf_token_time']) && 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    }
}

/**
 * Get CSRF token for AJAX requests
 */
function getCSRFTokenForAjax() {
    return generateCSRFToken();
}

/**
 * Verify CSRF token for AJAX requests
 */
function verifyCSRFTokenForAjax() {
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? null;
    
    if (!$token) {
        return false;
    }
    
    return verifyCSRFToken($token);
}

/**
 * Require CSRF token for AJAX requests
 */
function requireCSRFTokenForAjax() {
    if (!verifyCSRFTokenForAjax()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired security token']);
        exit();
    }
}

/**
 * Generate form with CSRF protection
 */
function generateFormWithCSRF($action, $method = 'POST', $attributes = []) {
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    $form = '<form action="' . htmlspecialchars($action) . '" method="' . htmlspecialchars($method) . '"' . $attrString . '>';
    $form .= generateCSRFToken();
    return $form;
}

/**
 * Close form tag
 */
function closeForm() {
    return '</form>';
}

/**
 * Generate complete form with CSRF protection
 */
function generateCompleteForm($action, $method = 'POST', $attributes = [], $content = '') {
    $form = generateFormWithCSRF($action, $method, $attributes);
    $form .= $content;
    $form .= closeForm();
    return $form;
}

/**
 * Validate CSRF token and return JSON response
 */
function validateCSRFAndReturnJSON($token) {
    if (!verifyCSRFToken($token)) {
        return [
            'success' => false,
            'message' => 'Invalid or expired security token',
            'error_code' => 'CSRF_TOKEN_INVALID'
        ];
    }
    
    return ['success' => true];
}

/**
 * Check if current request needs CSRF protection
 */
function needsCSRFProtection() {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    return in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']);
}

/**
 * Auto-verify CSRF for protected requests
 */
function autoVerifyCSRF() {
    if (needsCSRFProtection()) {
        if (!verifyCSRFTokenFromPost()) {
            $_SESSION['error'] = 'Invalid or expired security token. Please try again.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/');
            exit();
        }
    }
}

/**
 * Generate CSRF meta tag for JavaScript access
 */
function generateCSRFMetaTag() {
    $token = generateCSRFToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}

/**
 * Get CSRF token for JavaScript
 */
function getCSRFTokenForJS() {
    return generateCSRFToken();
}

/**
 * Verify CSRF token from multiple sources
 */
function verifyCSRFTokenFromMultipleSources() {
    // Check POST data
    if (isset($_POST['csrf_token'])) {
        return verifyCSRFToken($_POST['csrf_token']);
    }
    
    // Check headers
    $headers = getallheaders();
    if (isset($headers['X-CSRF-Token'])) {
        return verifyCSRFToken($headers['X-CSRF-Token']);
    }
    
    // Check GET parameter (for non-sensitive operations)
    if (isset($_GET['csrf_token'])) {
        return verifyCSRFToken($_GET['csrf_token']);
    }
    
    return false;
}

/**
 * Log CSRF token violations
 */
function logCSRFViolation($ip = null, $userAgent = null) {
    if (!$ip) {
        $ip = getClientIP();
    }
    
    if (!$userAgent) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    $logMessage = date('Y-m-d H:i:s') . " - CSRF Token Violation - IP: {$ip} - User-Agent: {$userAgent} - URL: " . getCurrentUrl();
    error_log($logMessage);
}

/**
 * Check if CSRF token is about to expire
 */
function isCSRFTokenExpiringSoon($minutes = 5) {
    if (!isset($_SESSION['csrf_token_time'])) {
        return true;
    }
    
    $timeLeft = CSRF_TOKEN_LIFETIME - (time() - $_SESSION['csrf_token_time']);
    return $timeLeft <= ($minutes * 60);
}

/**
 * Refresh CSRF token if expiring soon
 */
function refreshCSRFTokenIfNeeded() {
    if (isCSRFTokenExpiringSoon()) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        generateCSRFToken();
        return true;
    }
    
    return false;
}

/**
 * Get CSRF token expiration time
 */
function getCSRFTokenExpirationTime() {
    if (!isset($_SESSION['csrf_token_time'])) {
        return null;
    }
    
    return $_SESSION['csrf_token_time'] + CSRF_TOKEN_LIFETIME;
}

/**
 * Get remaining time for CSRF token
 */
function getCSRFTokenRemainingTime() {
    if (!isset($_SESSION['csrf_token_time'])) {
        return 0;
    }
    
    $remaining = CSRF_TOKEN_LIFETIME - (time() - $_SESSION['csrf_token_time']);
    return max(0, $remaining);
}

// Clean up expired tokens on script start
cleanupExpiredCSRFTokens();
