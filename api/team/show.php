<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
}

// Require admin authentication for viewing individual team member details
requireAdminLogin();

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid team member ID is required', 400);
}

try {
    global $db;
    // First check if team member exists
    $stmt = $db->prepare("SELECT id, name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts, created_at, updated_at FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendErrorResponse('Team member not found', 404);
    }

    $teamMember = $result->fetch_assoc();
    // Parse JSON fields
    $teamMember['social_links'] = json_decode($teamMember['social_links'], true) ?? [];
    $teamMember['skills'] = json_decode($teamMember['skills'], true) ?? [];
    $teamMember['portfolio_items'] = json_decode($teamMember['portfolio_items'], true) ?? [];
    $teamMember['blog_posts'] = json_decode($teamMember['blog_posts'], true) ?? [];

    sendSuccessResponse('Team member retrieved successfully', ['team_member' => $teamMember]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>