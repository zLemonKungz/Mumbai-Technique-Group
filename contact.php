<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development, restrict in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get environment variables with defaults
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: 3306;
$dbName = getenv('DB_NAME') ?: 'portfolio_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$contactTable = getenv('CONTACT_TABLE') ?: 'tdata';

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Database connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

// Check connection
if ($mysqli->connect_errno) {
    $response['message'] = 'Database connection failed: ' . $mysqli->connect_error;
    echo json_encode($response);
    exit;
}

// Prepare INSERT statement
$stmt = $mysqli->prepare("INSERT INTO `$contactTable` (name, email, subject, message) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    $response['message'] = 'Prepare failed: ' . $mysqli->error;
    $mysqli->close();
    echo json_encode($response);
    exit;
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Basic server-side validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $response['message'] = 'All fields are required.';
    $stmt->close();
    $mysqli->close();
    echo json_encode($response);
    exit;
}

// Bind parameters and execute
$stmt->bind_param('ssss', $name, $email, $subject, $message);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Message sent successfully!';
} else {
    $response['message'] = 'Insert failed: ' . $stmt->error;
}

// Clean up
$stmt->close();
$mysqli->close();

// Return JSON response
echo json_encode($response);
?>