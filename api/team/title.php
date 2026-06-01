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
    // Fetch team section title from team_settings
    $stmt = $db->prepare("SELECT `value` FROM team_settings WHERE `key` = 'team_title'");
    $stmt->execute();
    $result = $stmt->get_result();

    $title = 'Our Team'; // Default value
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['value'];
    }

    sendSuccessResponse('Team title retrieved successfully', ['title' => $title]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>