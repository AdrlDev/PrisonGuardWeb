<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authentication and Authorization Helper Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['email']) && !empty($_SESSION['email']);
}

/**
 * Check if user has a specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require login or redirect to login page
 */
function requireLogin($redirect_to = null) {
    if (!isLoggedIn()) {
        if ($redirect_to === null) {
            $redirect_to = '/Capstone/login/login-user.php';
        }
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Require specific role or redirect to appropriate error page
 */
function requireRole($required_role, $redirect_to = null) {
    requireLogin($redirect_to);
    if (!hasRole($required_role)) {
        if ($redirect_to === null) {
            $redirect_to = '/Capstone/login/unauthorized.php';
        }
        error_log("Access denied. Required role: $required_role, User role: " . ($_SESSION['role'] ?? 'none'));
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Require either warden or prison guard role
 */
function requireAnyRole($roles = ['warden', 'prison_guard'], $redirect_to = null) {
    requireLogin($redirect_to);
    $hasValidRole = false;
    foreach ($roles as $role) {
        if (hasRole($role)) {
            $hasValidRole = true;
            break;
        }
    }
    if (!$hasValidRole) {
        if ($redirect_to === null) {
            $redirect_to = '/Capstone/login/unauthorized.php';
        }
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['id'] ?? null,
        'name' => $_SESSION['name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}

/**
 * Get user's full name
 */
function getUserName() {
    return $_SESSION['name'] ?? 'User';
}

/**
 * Get user's role
 */
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Check if current user is warden
 */
function isWarden() {
    return hasRole('warden');
}

/**
 * Check if current user is prison guard
 */
function isGuard() {
    return hasRole('prison_guard') || hasRole('guard');
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Log user activity
 */
function logActivity($action, $details = '', $con_admin = null) {
    if (!$con_admin || !isLoggedIn()) {
        return false;
    }
    
    try {
        $stmt = $con_admin->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $user_id = $_SESSION['id'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->bind_param("issss", $user_id, $action, $details, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout user
 */
function logout($redirect_to = '../login-user.php') {
    // Log the logout activity
    if (isset($_SESSION['name'])) {
        error_log("User " . $_SESSION['name'] . " is logging out");
    }
    
    // Destroy all session data
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: ' . $redirect_to);
    exit();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>