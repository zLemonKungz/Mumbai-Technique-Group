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
    $stmt = $db->prepare("SELECT p.*, tm.name as member_name, tm.role FROM team_portfolio p LEFT JOIN team_members tm ON p.team_member_id = tm.id WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendErrorResponse('Portfolio item not found', 404);
    }

    $portfolioItem = $result->fetch_assoc();
    sendSuccessResponse('Portfolio item retrieved', ['portfolio_item' => $portfolioItem]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>