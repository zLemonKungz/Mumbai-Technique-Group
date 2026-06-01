<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendErrorResponse('Method not allowed', 405);
}

// Require admin authentication
requireAdminLogin();

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid team member ID is required', 400);
}

// Get and decode JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

// Prepare data for update
$name = isset($sanitized['name']) ? $sanitized['name'] : null;
$role = isset($sanitized['role']) ? $sanitized['role'] : null;
$bio = isset($sanitized['bio']) ? $sanitized['bio'] : null;
$avatar_url = isset($sanitized['avatar_url']) ? $sanitized['avatar_url'] : null;
$email = isset($sanitized['email']) ? $sanitized['email'] : null;
$social_links = isset($sanitized['social_links']) ? json_encode($sanitized['social_links']) : null;
$skills = isset($sanitized['skills']) ? json_encode($sanitized['skills']) : null;
$portfolio_items = isset($sanitized['portfolio_items']) ? json_encode($sanitized['portfolio_items']) : null;
$blog_posts = isset($sanitized['blog_posts']) ? json_encode($sanitized['blog_posts']) : null;

// Build dynamic update query
$updates = [];
$params = [];
$types = '';

if ($name !== null) {
    $updates[] = "name = ?";
    $params[] = $name;
    $types .= 's';
}

if ($role !== null) {
    $updates[] = "role = ?";
    $params[] = $role;
    $types .= 's';
}

if ($bio !== null) {
    $updates[] = "bio = ?";
    $params[] = $bio;
    $types .= 's';
}

if ($avatar_url !== null) {
    $updates[] = "avatar_url = ?";
    $params[] = $avatar_url;
    $types .= 's';
}

if ($email !== null) {
    $updates[] = "email = ?";
    $params[] = $email;
    $types .= 's';
}

if ($social_links !== null) {
    $updates[] = "social_links = ?";
    $params[] = $social_links;
    $types .= 's';
}

if ($skills !== null) {
    $updates[] = "skills = ?";
    $params[] = $skills;
    $types .= 's';
}

if ($portfolio_items !== null) {
    $updates[] = "portfolio_items = ?";
    $params[] = $portfolio_items;
    $types .= 's';
}

if ($blog_posts !== null) {
    $updates[] = "blog_posts = ?";
    $params[] = $blog_posts;
    $types .= 's';
}

// Add the updated_at timestamp
$updates[] = "updated_at = CURRENT_TIMESTAMP";
$params[] = $id;
$types .= 'i';

if (empty($updates)) {
    sendErrorResponse('No valid fields to update', 400);
}

try {
    global $db;
    $query = "UPDATE team_members SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        sendErrorResponse('Team member not found or no changes made', 404);
    }

    sendSuccessResponse('Team member updated successfully');
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>