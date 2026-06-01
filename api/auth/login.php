<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Get and decode JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

$username = isset($sanitized['username']) ? $sanitized['username'] : '';
$password = isset($sanitized['password']) ? $sanitized['password'] : '';

if (empty($username) || empty($password)) {
    sendErrorResponse('Username and password are required', 400);
}

// Attempt admin login
if (adminLogin($username, $password)) {
    sendSuccessResponse('Login successful', ['username' => $username]);
} else {
    sendErrorResponse('Invalid credentials', 401);
}
?>