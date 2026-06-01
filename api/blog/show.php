<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid portfolio item ID is required', 400);
}

try {
    global $db;
    $stmt = $db->prepare("SELECT b.*, tm.name as member_name FROM team_blog b LEFT JOIN team_members tm ON b.team_member_id = tm.id WHERE b.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendErrorResponse('Blog post not found', 404);
    }

    $blogPost = $result->fetch_assoc();
    if (isset($blogPost['tags']) && $blogPost['tags']) {
        $blogPost['tags'] = json_decode($blogPost['tags'], true);
    } else {
        $blogPost['tags'] = [];
    }

    sendSuccessResponse('Blog post retrieved', ['blog_post' => $blogPost]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>