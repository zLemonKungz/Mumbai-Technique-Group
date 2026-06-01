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
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $featured = isset($_GET['featured']) ? intval($_GET['featured']) : null;

    $sql = "SELECT p.*, tm.name as member_name FROM team_portfolio p LEFT JOIN team_members tm ON p.team_member_id = tm.id WHERE 1=1";
    $params = [];
    $types = '';

    if ($teamMemberId) {
        $sql .= " AND p.team_member_id = ?";
        $params[] = $teamMemberId;
        $types .= 'i';
    }
    if ($category) {
        $sql .= " AND p.category = ?";
        $params[] = $category;
        $types .= 's';
    }
    if ($featured !== null) {
        $sql .= " AND p.featured = ?";
        $params[] = $featured;
        $types .= 'i';
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $portfolioItems = [];
    while ($row = $result->fetch_assoc()) {
        $portfolioItems[] = $row;
    }

    sendSuccessResponse('Portfolio items retrieved', ['portfolio_items' => $portfolioItems]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>