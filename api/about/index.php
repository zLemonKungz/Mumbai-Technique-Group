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
    // Fetch about content from team_settings or a dedicated about table
    // For now, we'll use a simple approach with team_settings
    $stmt = $db->prepare("SELECT `key`, `value` FROM team_settings WHERE `key` LIKE 'about_%'");
    $stmt->execute();
    $result = $stmt->get_result();

    $aboutData = [];
    while ($row = $result->fetch_assoc()) {
        $key = str_replace('about_', '', $row['key']);
        $aboutData[$key] = $row['value'];
    }

    // If no settings found, provide default content
    if (empty($aboutData)) {
        $aboutData = [
            'title' => 'About Us',
            'content' => '<p>We are a creative team passionate about delivering exceptional digital experiences. Our multidisciplinary approach combines thoughtful design, robust development, and strategic thinking to create solutions that engage users and drive business results.</p><p>With years of experience across various industries, we pride ourselves on our ability to understand complex challenges and transform them into elegant, functional solutions that exceed expectations.</p>'
        ];
    }

    // Ensure we have title and content
    if (!isset($aboutData['title'])) {
        $aboutData['title'] = 'About Us';
    }
    if (!isset($aboutData['content'])) {
        $aboutData['content'] = '<p>We are a creative team passionate about delivering exceptional digital experiences.</p>';
    }

    sendSuccessResponse('About content retrieved successfully', $aboutData);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>