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

requireAdminLogin();

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

if (!isset($data['title']) || empty($data['title'])) {
    sendErrorResponse('Title is required');
}
if (!isset($data['content']) || empty($data['content'])) {
    sendErrorResponse('Content is required');
}

$sanitized = sanitizeInput($data);

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO team_blog (team_member_id, title, content, excerpt, image_url, published_at, tags) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $publishDate = date('Y-m-d H:i:s');
    $featured = isset($sanitized['featured']) ? intval($sanitized['featured']) : 0;

    // Handle tags - ensure they're properly encoded
    $tags = isset($sanitized['tags']) ? json_encode($sanitized['tags']) : '[]';

    // For excerpt, we'll take first 150 characters of content
    $excerpt = strlen($sanitized['content']) > 150 ? substr($sanitized['content'], 0, 150) . '...' : $sanitized['content'];

    $stmt->bind_param("issssss",
        $sanitized['team_member_id'] ?? null,
        $sanitized['title'] ?? null,
        $sanitized['content'] ?? null,
        $excerpt,
        $sanitized['image_url'] ?? null,
        $publishDate,
        $tags
    );
    $stmt->execute();

    $insertId = $stmt->insert_id;
    sendSuccessResponse('Blog post created successfully', ['blog_post_id' => $insertId], 201);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>