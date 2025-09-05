<?php
/**
 * Authentication Class
 * Handles user authentication, registration, and role management
 */

require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    private $user;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadCurrentUser();
    }

    /**
     * Load current user from session
     */
    private function loadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $sql = "SELECT user_id, name, email, role, phone, created_at FROM users WHERE user_id = :user_id";
            $this->user = $this->db->fetchOne($sql, ['user_id' => $_SESSION['user_id']]);
        }
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        return $this->user;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->user !== null;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        return $this->user && $this->user['role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return $this->user && in_array($this->user['role'], $roles);
    }

    /**
     * Require specific role (redirect if not)
     */
    public function requireRole($role) {
        if (!$this->hasRole($role)) {
            $_SESSION['error'] = 'Access denied. Insufficient permissions.';
            header('Location: ../login.php');
            exit();
        }
    }

    /**
     * Require any of the specified roles
     */
    public function requireAnyRole($roles) {
        if (!$this->hasAnyRole($roles)) {
            $_SESSION['error'] = 'Access denied. Insufficient permissions.';
            header('Location: ../login.php');
            exit();
        }
    }

    /**
     * User login
     */
    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'Email and password are required'];
            }

            if (!isValidEmail($email)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Get user by email
            $sql = "SELECT user_id, name, email, password, role, phone FROM users WHERE email = :email AND is_active = 1";
            $user = $this->db->fetchOne($sql, ['email' => $email]);

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'user_id = :user_id', 
                ['user_id' => $user['user_id']]
            );

            // Log activity
            logActivity('login', 'User logged in successfully', $user['user_id']);

            return ['success' => true, 'user' => $user];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * User registration
     */
    public function register($data) {
        try {
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'password', 'phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }

            // Validate email
            if (!isValidEmail($data['email'])) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Validate phone
            if (!isValidPhone($data['phone'])) {
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }

            // Validate password
            if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }

            // Validate role
            // Role is fixed to student for public registration
            $data['role'] = 'student';

            // Check if email already exists
            if ($this->db->exists('users', 'email = :email', ['email' => $data['email']])) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Prepare user data
            $userData = [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'],
                'role' => $data['role'],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert user
            $userId = $this->db->insert('users', $userData);

            if ($userId) {
                // Log activity
                logActivity('register', 'New user registered: ' . $userData['name'], $userId);

                return [
                    'success' => true, 
                    'message' => 'Registration successful! You can now login.',
                    'user_id' => $userId
                ];
            } else {
                return ['success' => false, 'message' => 'Registration failed. Please try again.'];
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * User logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log activity
            logActivity('logout', 'User logged out', $_SESSION['user_id']);
        }

        // Destroy session
        session_destroy();
        
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get user
            $sql = "SELECT password FROM users WHERE user_id = :user_id";
            $user = $this->db->fetchOne($sql, ['user_id' => $userId]);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Validate new password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $this->db->update('users', 
                ['password' => $hashedPassword], 
                'user_id = :user_id', 
                ['user_id' => $userId]
            );

            // Log activity
            logActivity('change_password', 'Password changed successfully', $userId);

            return ['success' => true, 'message' => 'Password changed successfully'];

        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to change password. Please try again.'];
        }
    }

    /**
     * Reset password (admin function)
     */
    public function resetPassword($userId, $newPassword) {
        try {
            // Validate password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }

            // Hash password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $this->db->update('users', 
                ['password' => $hashedPassword], 
                'user_id = :user_id', 
                ['user_id' => $userId]
            );

            // Log activity
            logActivity('reset_password', 'Password reset by admin', $userId);

            return ['success' => true, 'message' => 'Password reset successfully'];

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reset password. Please try again.'];
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            // Validate email if provided
            if (isset($data['email']) && !isValidEmail($data['email'])) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Validate phone if provided
            if (isset($data['phone']) && !isValidPhone($data['phone'])) {
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }

            // Check if email already exists (if changing email)
            if (isset($data['email'])) {
                $sql = "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id";
                $existing = $this->db->fetchOne($sql, ['email' => $data['email'], 'user_id' => $userId]);
                if ($existing) {
                    return ['success' => false, 'message' => 'Email already registered by another user'];
                }
            }

            // Update user
            $this->db->update('users', $data, 'user_id = :user_id', ['user_id' => $userId]);

            // Log activity
            logActivity('update_profile', 'Profile updated', $userId);

            return ['success' => true, 'message' => 'Profile updated successfully'];

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update profile. Please try again.'];
        }
    }

    /**
     * Deactivate user
     */
    public function deactivateUser($userId) {
        try {
            $this->db->update('users', 
                ['is_active' => 0], 
                'user_id = :user_id', 
                ['user_id' => $userId]
            );

            // Log activity
            logActivity('deactivate_user', 'User deactivated', $userId);

            return ['success' => true, 'message' => 'User deactivated successfully'];

        } catch (Exception $e) {
            error_log("Deactivate user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to deactivate user. Please try again.'];
        }
    }

    /**
     * Activate user
     */
    public function activateUser($userId) {
        try {
            $this->db->update('users', 
                ['is_active' => 1], 
                'user_id = :user_id', 
                ['user_id' => $userId]
            );

            // Log activity
            logActivity('activate_user', 'User activated', $userId);

            return ['success' => true, 'message' => 'User activated successfully'];

        } catch (Exception $e) {
            error_log("Activate user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to activate user. Please try again.'];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $sql = "SELECT user_id, name, email, role, phone, is_active, created_at, last_login FROM users WHERE user_id = :user_id";
        return $this->db->fetchOne($sql, ['user_id' => $userId]);
    }

    /**
     * Get all users (with pagination)
     */
    public function getAllUsers($page = 1, $perPage = 20, $search = '') {
        $where = '1';
        $params = [];

        if (!empty($search)) {
            $where = $this->db->buildSearchWhere(['name', 'email', 'role'], $search);
            $params['search'] = '%' . $search . '%';
        }

        $sql = "SELECT user_id, name, email, role, phone, is_active, created_at, last_login FROM users WHERE {$where} ORDER BY created_at DESC";
        
        return $this->db->paginate($sql, $page, $perPage, $params);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role, $page = 1, $perPage = 20) {
        $sql = "SELECT user_id, name, email, phone, is_active, created_at, last_login FROM users WHERE role = :role ORDER BY created_at DESC";
        $params = ['role' => $role];
        
        return $this->db->paginate($sql, $page, $perPage, $params);
    }

    /**
     * Check if user exists
     */
    public function userExists($userId) {
        return $this->db->exists('users', 'user_id = :user_id', ['user_id' => $userId]);
    }

    /**
     * Get user count by role
     */
    public function getUserCountByRole($role) {
        return $this->db->count('users', 'role = :role', ['role' => $role]);
    }

    /**
     * Get total user count
     */
    public function getTotalUserCount() {
        return $this->db->count('users');
    }
}
