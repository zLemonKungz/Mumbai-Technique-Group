<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendErrorResponse('Method not allowed', 405);
}

// Require admin authentication
requireAdminLogin();

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid team member ID is required', 400);
}

try {
    global $db;
    // First check if team member exists
    $stmt = $db->prepare("SELECT id FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendErrorResponse('Team member not found', 404);
    }

    // Delete team member
    $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    sendSuccessResponse('Team member deleted successfully');
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>