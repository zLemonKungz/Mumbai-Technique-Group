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

// Require admin authentication
requireAdminLogin();

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

// Prepare data for insert
$name = isset($sanitized['name']) ? $sanitized['name'] : null;
$role = isset($sanitized['role']) ? $sanitized['role'] : null;
$bio = isset($sanitized['bio']) ? $sanitized['bio'] : null;
$avatar_url = isset($sanitized['avatar_url']) ? $sanitized['avatar_url'] : null;
$email = isset($sanitized['email']) ? $sanitized['email'] : null;
$social_links = isset($sanitized['social_links']) ? json_encode($sanitized['social_links']) : null;
$skills = isset($sanitized['skills']) ? json_encode($sanitized['skills']) : null;
$portfolio_items = isset($sanitized['portfolio_items']) ? json_encode($sanitized['portfolio_items']) : null;
$blog_posts = isset($sanitized['blog_posts']) ? json_encode($sanitized['blog_posts']) : null;

if ($name === null) {
    sendErrorResponse('Team member name is required', 400);
}

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO team_members (name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $role, $bio, $avatar_url, $email, $social_links, $skills, $portfolio_items, $blog_posts);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        sendErrorResponse('Failed to create team member', 500);
    }

    $newId = $stmt->insert_id;
    sendSuccessResponse('Team member created successfully', ['id' => $newId]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>