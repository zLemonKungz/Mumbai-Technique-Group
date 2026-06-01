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

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid blog post ID is required', 400);
}

// Require admin authentication
requireAdminLogin();

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

// Prepare data for update
$title = isset($sanitized['title']) ? $sanitized['title'] : null;
$content = isset($sanitized['content']) ? $sanitized['content'] : null;
$image_url = isset($sanitized['image_url']) ? $sanitized['image_url'] : null;
$category = isset($sanitized['category']) ? $sanitized['category'] : null;
$tags = isset($sanitized['tags']) ? json_encode($sanitized['tags']) : null;

// Build dynamic update query
$updates = [];
$params = [];
$types = '';

if ($title !== null) {
    $updates[] = "title = ?";
    $params[] = $title;
    $types .= 's';
}

if ($content !== null) {
    $updates[] = "content = ?";
    $params[] = $content;
    $types .= 's';
}

if ($image_url !== null) {
    $updates[] = "image_url = ?";
    $params[] = $image_url;
    $types .= 's';
}

if ($category !== null) {
    $updates[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($tags !== null) {
    $updates[] = "tags = ?";
    $params[] = $tags;
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
    $query = "UPDATE team_blog SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        sendErrorResponse('Blog post not found or no changes made', 404);
    }

    sendSuccessResponse('Blog post updated successfully');
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>