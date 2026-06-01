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
if (!isset($data['team_member_id']) || empty($data['team_member_id'])) {
    sendErrorResponse('Team member ID is required');
}

$sanitized = sanitizeInput($data);

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO team_portfolio (team_member_id, title, description, image_url, category, project_url, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $featured = isset($sanitized['featured']) ? intval($sanitized['featured']) : 0;
    $stmt->bind_param("isssssi",
        $sanitized['team_member_id'],
        $sanitized['title'],
        $sanitized['description'] ?? '',
        $sanitized['image_url'] ?? '',
        $sanitized['category'] ?? '',
        $sanitized['project_url'] ?? '',
        $featured
    );
    $stmt->execute();

    $insertId = $stmt->insert_id;
    sendSuccessResponse('Portfolio item created successfully', ['portfolio_item_id' => $insertId], 201);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>