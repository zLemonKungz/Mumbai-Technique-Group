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
    $teamMemberId = isset($_GET['team_member_id']) ? intval($_GET['team_member_id']) : null;
    $tag = isset($_GET['tag']) ? $_GET['tag'] : null;

    $sql = "SELECT b.*, tm.name as member_name FROM team_blog b LEFT JOIN team_members tm ON b.team_member_id = tm.id WHERE 1=1";
    $params = [];
    $types = '';

    if ($teamMemberId) {
        $sql .= " AND b.team_member_id = ?";
        $params[] = $teamMemberId;
        $types .= 'i';
    }
    if ($tag) {
        $sql .= " AND JSON_CONTAINS(b.tags, ?)";
        $params[] = json_encode([$tag]);
        $types .= 's';
    }

    $sql .= " ORDER BY b.published_at DESC, b.created_at DESC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $blogPosts = [];
    while ($row = $result->fetch_assoc()) {
        if (isset($row['tags']) && $row['tags']) {
            $row['tags'] = json_decode($row['tags'], true);
        } else {
            $row['tags'] = [];
        }
        $blogPosts[] = $row;
    }

    sendSuccessResponse('Blog posts retrieved', ['blog_posts' => $blogPosts]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>