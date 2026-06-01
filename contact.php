<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development, restrict in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?: '';

// Basic server-side validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    sendErrorResponse('All fields are required.');
}

// Sanitize input data
$sanitized = sanitizeInput([
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message
]);

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO contact_form_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $sanitized['name'], $sanitized['email'], $sanitized['subject'], $sanitized['message']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        sendSuccessResponse('Message sent successfully!');
    } else {
        sendErrorResponse('Failed to send message');
    }
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage());
}
?>