<?php
/**
 * Helper functions for API responses and data handling
 */

/**
 * Send JSON success response
 *
 * @param string $message Success message
 * @param array|null $data Optional data array
 */
function sendSuccessResponse($message, $data = null) {
    $response = [
        'success' => true,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Send JSON error response
 *
 * @param string $message Error message
 * @param int $code HTTP status code
 */
function sendErrorResponse($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/**
 * Sanitize input data to prevent XSS and SQL injection
 *
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_string($data)) {
        // Remove whitespace and sanitize
        $data = trim($data);
        // Strip HTML tags (allow some safe tags if needed)
        $data = strip_tags($data);
        // Escape special characters for HTML
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    } elseif (is_array($data)) {
        // Recursively sanitize array elements
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = sanitizeInput($value);
        }
        return $sanitized;
    } else {
        // Return other types as-is (int, bool, null)
        return $data;
    }
}

/**
 * Validate email format
 *
 * @param string $email Email to validate
 * @return bool True if valid email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate CSRF token
 *
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 *
 * @param string $token Token to validate
 * @return bool True if token is valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>