<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
}

try {
    global $db;
    $stmt = $db->prepare("SELECT id, name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts, created_at FROM team_members ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();

    $teamMembers = [];
    while ($row = $result->fetch_assoc()) {
        // Parse JSON fields
        $row['social_links'] = json_decode($row['social_links'], true) ?? [];
        $row['skills'] = json_decode($row['skills'], true) ?? [];
        $row['portfolio_items'] = json_decode($row['portfolio_items'], true) ?? [];
        $row['blog_posts'] = json_decode($row['blog_posts'], true) ?? [];
        $teamMembers[] = $row;
    }

    sendSuccessResponse('Team members retrieved successfully', ['team_members' => $teamMembers]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>