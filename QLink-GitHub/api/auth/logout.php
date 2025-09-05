<?php
require_once '../../includes/config.php';
require_once '../../includes/Auth.php';

// Initialize auth
$auth = new Auth();

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    logActivity('user_logout', "User logged out", $_SESSION['user_id']);
}

// Perform logout
$auth->logout();

// Set success message
$_SESSION['success'] = 'You have been logged out successfully.';

// Redirect to login page
header('Location: ../../login.php');
exit();
?>
