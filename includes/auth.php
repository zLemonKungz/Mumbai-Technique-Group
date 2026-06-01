<?php
session_start();

// Admin credentials - in a real application, these would be stored securely in the database
$ADMIN_USERNAME = getenv('ADMIN_USERNAME') ?: 'admin';
$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: 'password123'; // Should be hashed in production

/**
 * Check if admin is logged in
 *
 * @return bool True if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login - redirect to login if not authenticated
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // For API requests, return unauthorized instead of redirecting
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
            http_response_code(401);
            exit;
        } else {
            // For direct browser requests, redirect to login page
            header('Location: /admin/login.php');
            exit;
        }
    }
}

/**
 * Admin login function
 *
 * @param string $username Username to check
 * @param string $password Password to check
 * @return bool True if login successful
 */
function adminLogin($username, $password) {
    global $ADMIN_USERNAME, $ADMIN_PASSWORD;

    // In production, use password_verify with hashed password
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        return true;
    }
    return false;
}

/**
 * Admin logout function
 */
function adminLogout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>