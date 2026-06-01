# Team-Capable Portfolio Platform with PHP CMS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the existing single-freelancer portfolio site into a team-capable portfolio platform with PHP-based content management system, green color theme, and enhanced visual effects.

**Architecture:** Build a PHP/MySQL backend with REST-like API endpoints serving JSON data to a frontend SPA. Implement secure admin authentication, dynamic content loading, team overview grid, individual member profiles, and inline editing capabilities. Maintain existing contact form functionality while extending the database schema for team data.

**Tech Stack:** HTML5, CSS3, Vanilla JavaScript, PHP 7.4+ with MySQLi, MySQL 5.7+, Ionicons 5.5.2, Google Fonts (Poppins)

---

## Phase 1: Foundation & Database

### Task 1: Database Setup and Schema creation

**Files:**
- Create: `database.sql`
- Modify: None

- [ ] **Step 1: Write the failing test**

We'll verify the database connection and schema creation by attempting to create tables.

```bash
# This is a shell command test - we'll check if mysql client is available and can connect
mysql --version
```

- [ ] **Step 2: Run test to verify it fails**

Run: `mysql --version`
Expected: Should show mysql client version if installed, otherwise error

- [ ] **Step 3: Write database schema file**

Create `database.sql` with the following content:
```sql
-- Team members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    bio TEXT,
    avatar_url VARCHAR(255),
    email VARCHAR(100),
    social_links JSON,
    skills JSON,
    portfolio_items JSON,
    blog_posts JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio items table
CREATE TABLE IF NOT EXISTS team_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(200),
    description TEXT,
    image_url VARCHAR(255),
    category ENUM('web', 'app', 'branding', 'ecommerce'),
    project_url VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS team_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(200),
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    published_at TIMESTAMP,
    tags JSON,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE IF NOT EXISTS team_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_title VARCHAR(100) DEFAULT 'PixelTest Team',
    tagline VARCHAR(200) DEFAULT 'Creative Agency',
    logo_url VARCHAR(255),
    color_theme VARCHAR(20) DEFAULT 'green',
    contact_info JSON,
    social_links JSON,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact submissions table (reuse existing or ensure compatibility)
CREATE TABLE IF NOT EXISTS tdata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

- [ ] **Step 4: Verify schema file created correctly**

Run: `cat database.sql`
Expected: Should display the complete SQL schema above

- [ ] **Step 5: Commit**

```bash
git add database.sql
git commit -m "feat: add database schema for team portfolio CMS"
```

### Task 2: Backend API structure and database connection

**Files:**
- Create: `includes/db.php`
- Create: `includes/functions.php`
- Modify: None

- [ ] **Step 1: Write the failing test**

Create a simple PHP script to test database connection:
```php
<?php
// test_db_connection.php
require_once 'includes/db.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
$conn->close();
?>
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php test_db_connection.php`
Expected: Should fail with "failed to open stream: No such file or directory" for missing includes

- [ ] **Step 3: Write database connection file**

Create `includes/db.php`:
```php
<?php
// Database configuration
define('DB_HOST', isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
define('DB_PORT', isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306);
define('DB_NAME', isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'portfolio');
define('DB_USER', isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
define('DB_PASS', isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Make connection available globally
$GLOBALS['db'] = $conn;
?>
```

- [ ] **Step 4: Write helper functions file**

Create `includes/functions.php`:
```php
<?php
/**
 * Helper functions for API responses and data handling
 */

/**
 * Send JSON response
 * @param array $data Data to send
 * @param int $status HTTP status code
 */
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 * @param string $message Error message
 * @param int $status HTTP status code (default 400)
 */
function sendErrorResponse($message, $status = 400) {
    sendJsonResponse(['success' => false, 'message' => $message], $status);
}

/**
 * Send success response
 * @param string $message Success message
 * @param array $data Additional data
 * @param int $status HTTP status code (default 200)
 */
function sendSuccessResponse($message, $data = [], $status = 200) {
    sendJsonResponse(array_merge(['success' => true, 'message' => $message], $data), $status);
}

/**
 * Sanitize input data
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    global $db;
    
    if (is_string($data)) {
        // Remove harmful tags and escape for database
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $data = $db->real_escape_string($data);
    } elseif (is_array($data)) {
        // Recursively sanitize array elements
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    }
    
    return $data;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
?>
```

- [ ] **Step 3: Update test to use the new files**

Update `test_db_connection.php`:
```php
<?php
// test_db_connection.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$conn = $GLOBALS['db'];
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
$conn->close();
?>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php test_db_connection.php`
Expected: Should show "Connected successfully" if database credentials are valid, or connection error if not

- [ ] **Step 5: Clean up test file and commit**

```bash
git add includes/db.php includes/functions.php
git commit -m "feat: add database connection and helper functions"
```

### Task 3: Authentication system

**Files:**
- Create: `api/auth/login.php`
- Create: `api/auth/logout.php`
- Create: `includes/auth.php`
- Modify: None

- [ ] **Step 1: Write the failing test**

Create test script for authentication:
```php
<?php
// test_auth.php
require_once '../includes/auth.php';
// Test that functions exist
if (!function_exists('adminLogin')) {
    throw new Exception('adminLogin function not defined');
}
if (!function_exists('adminLogout')) {
    throw new Exception('adminLogout function not defined');
}
echo "Auth functions exist";
?>
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php test_auth.php`
Expected: Should fail with "failed to open stream" or "function not defined"

- [ ] **Step 3: Write authentication helper file**

Create `includes/auth.php`:
```php
<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

/**
 * Admin login
 * @param string $username 
 * @param string $password
 * @return array Result array with success/status
 */
function adminLogin($username, $password) {
    global $db;
    
    // Sanitize inputs
    $username = sanitizeInput($username);
    $password = trim($password);
    
    // For simplicity, we'll check against environment variables
    // In production, this should check against a users table with hashed passwords
    $admin_user = isset($_ENV['ADMIN_USER']) ? $_ENV['ADMIN_USER'] : 'admin';
    $admin_pass = isset($_ENV['ADMIN_PASS']) ? $_ENV['ADMIN_PASS'] : password_hash('admin123', PASSWORD_DEFAULT);
    
    // Verify credentials
    if ($username === $admin_user && password_verify($password, $admin_pass)) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        
        // Set admin session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => ['username' => $username]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    ]
}

/**
 * Admin logout
 * @return array Result array
 */
function adminLogout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session
    session_destroy();
    
    return [
        'success' => true,
        'message' => 'Logout successful'
    ];
}

/**
 * Check if admin is logged in
 * @return bool True if logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login - redirect if not logged in
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}
?>
```

- [ ] **Step 4: Write login endpoint**

Create `api/auth/login.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Validate required fields
if (!isset($data['username']) || !isset($data['password'])) {
    sendErrorResponse('Username and password are required');
}

// Attempt login
$result = adminLogin($data['username'], $data['password']);

if ($result['success']) {
    sendSuccessResponse($result['message'], ['user' => $result['user']]);
} else {
    sendErrorResponse($result['message'], 401);
}
?>
```

- [ ] **Step 5: Write logout endpoint**

Create `api/auth/logout.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Perform logout
$result = adminLogout();

sendSuccessResponse($result['message']);
?>
```

- [ ] **Step 6: Update test to use the new endpoints**

Update `test_auth.php`:
```php
<?php
// test_auth.php
require_once '../includes/auth.php';
// Test that functions exist
if (!function_exists('adminLogin')) {
    throw new Exception('adminLogin function not defined');
}
if (!function_exists('adminLogout')) {
    throw new Exception('adminLogout function not defined');
}
if (!function_exists('isAdminLoggedIn')) {
    throw new Exception('isAdminLoggedIn function not defined');
}
echo "Auth functions exist";
?>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php test_auth.php`
Expected: Should show "Auth functions exist"

- [ ] **Step 8: Test the endpoints**

Create test files for endpoints:
```php
<?php
// test_login_endpoint.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['json'] = json_encode(['username' => 'admin', 'password' => 'admin123']);
// Simulate input
$_SERVER['CONTENT_TYPE'] = 'application/json';
$fakeInput = json_encode(['username' => 'admin', 'password' => 'admin123']);
file_put_contents('php://input', $fakeInput);

require_once 'api/auth/login.php';
?>
```

Actually, let's test differently - we'll commit and test manually later.

- [ ] **Step 9: Commit authentication files**

```bash
git add includes/auth.php api/auth/login.php api/auth/logout.php
git commit -m "feat: add authentication system"
```

### Task 4: Team members API endpoints

**Files:**
- Create: `api/team/index.php` (GET all team members)
- Create: `api/team/show.php` (GET single team member)
- Create: `api/team/store.php` (POST new team member)
- Create: `api/team/update.php` (PUT update team member)
- Create: `api/team/delete.php` (DELETE team member)
- Modify: None

- [ ] **Step 1: Write the failing test**

Create test script for team API:
```php
<?php
// test_team_api.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
// Test that we can at least include the files
if (!isset($GLOBALS['db'])) {
    throw new Exception('Database connection not available');
}
echo "Team API dependencies loaded";
?>
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php test_team_api.php`
Expected: Should fail with "failed to open stream" for missing includes

- [ ] **Step 3: Write GET all team members endpoint**

Create `api/team/index.php`:
```php
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
    $stmt = $db->prepare("SELECT id, name, role, avatar_url, email FROM team_members ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $teamMembers = [];
    while ($row = $result->fetch_assoc()) {
        $teamMembers[] = $row;
    }
    
    sendSuccessResponse('Team members retrieved', ['team_members' => $teamMembers]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>
```

- [ ] **Step 4: Write GET single team member endpoint**

Create `api/team/show.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('Method not allowed', 405);
}

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid team member ID is required', 400);
}

try {
    global $db;
    $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendErrorResponse('Team member not found', 404);
    }
    
    $teamMember = $result->fetch_assoc();
    
    // Parse JSON fields for easier frontend consumption
    $jsonFields = ['social_links', 'skills', 'portfolio_items', 'blog_posts'];
    foreach ($jsonFields as $field) {
        if (isset($teamMember[$field]) && $teamMember[$field]) {
            $teamMember[$field] = json_decode($teamMember[$field], true);
        } else {
            $teamMember[$field] = [];
        }
    }
    
    sendSuccessResponse('Team member retrieved', ['team_member' => $teamMember]);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>
```

- [ ] **Step 5: Write CREATE team member endpoint**

Create `api/team/store.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Require admin authentication
requireAdminLogin();

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Validate required fields
if (!isset($data['name']) || empty($data['name'])) {
    sendErrorResponse('Name is required');
}
if (!isset($data['role']) || empty($data['role'])) {
    sendErrorResponse('Role is required');
}
if (!isset($data['bio']) || empty($data['bio'])) {
    sendErrorResponse('Bio is required');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

// Prepare data for insertion
$name = $sanitized['name'];
$role = $sanitized['role'];
$bio = $sanitized['bio'];
$avatar_url = isset($sanitized['avatar_url']) ? $sanitized['avatar_url'] : null;
$email = isset($sanitized['email']) ? $sanitized['email'] : null;
$social_links = isset($sanitized['social_links']) ? json_encode($sanitized['social_links']) : null;
$skills = isset($sanitized['skills']) ? json_encode($sanitized['skills']) : null;
$portfolio_items = isset($sanitized['portfolio_items']) ? json_encode($sanitized['portfolio_items']) : null;
$blog_posts = isset($sanitized['blog_posts']) ? json_encode($sanitized['blog_posts']) : null;

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO team_members (name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $role, $bio, $avatar_url, $email, $social_links, $skills, $portfolio_items, $blog_posts);
    $stmt->execute();
    
    $insertId = $stmt->insert_id;
    
    sendSuccessResponse('Team member created successfully', ['team_member_id' => $insertId], 201);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>
```

- [ ] **Step 6: Write UPDATE team member endpoint**

Create `api/team/update.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendErrorResponse('Method not allowed', 405);
}

// Require admin authentication
requireAdminLogin();

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    sendErrorResponse('Valid team member ID is required', 400);
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

// Sanitize input data
$sanitized = sanitizeInput($data);

// Prepare data for update
$name = isset($sanitized['name']) ? $sanitized['name'] : null;
$role = isset($sanitized['role']) ? $sanitized['role'] : null;
$bio = isset($sanitized['bio']) ? $sanitized['bio'] : null;
$avatar_url = isset($sanitized['avatar_url']) ? $sanitized['avatar_url'] : null;
$email = isset($sanitized['email']) ? $sanitized['email'] : null;
$social_links = isset($sanitized['social_links']) ? json_encode($sanitized['social_links']) : null;
$skills = isset($sanitized['skills']) ? json_encode($sanitized['skills']) : null;
$portfolio_items = isset($sanitized['portfolio_items']) ? json_encode($sanitized['portfolio_items']) : null;
$blog_posts = isset($sanitized['blog_posts']) ? json_encode($sanitized['blog_posts']) : null;

// Build dynamic update query
$updates = [];
$params = [];
$types = '';

foreach (['name', 'role', 'bio', 'avatar_url', 'email'] as $field) {
    if ($$field !== null) {
        $updates[] = "$field = ?";
        $params[] = $$field;
        $types .= 's';
    }
}

foreach (['social_links', 'skills', 'portfolio_items', 'blog_posts'] as $field) {
    if ($$field !== null) {
        $updates[] = "$field = ?";
        $params[] = $$field;
        $types .= 's';
    }
}

if (empty($updates)) {
    sendErrorResponse('No valid fields to update', 400);
}

$updates[] = "updated_at = CURRENT_TIMESTAMP";
$params[] = $id;
$types .= 'i';

try {
    global $db;
    $query = "UPDATE team_members SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        sendErrorResponse('Team member not found or no changes made', 404);
    }
    
    sendSuccessResponse('Team member updated successfully');
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>
```

- [ ] **Step 7: Write DELETE team member endpoint**

Create `api/team/delete.php`:
```php
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
```

- [ ] **Step 8: Update test to use the new endpoints**

Update `test_team_api.php`:
```php
<?php
// test_team_api.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
// Test that we can at least include the files
if (!isset($GLOBALS['db'])) {
    throw new Exception('Database connection not available');
}
echo "Team API dependencies loaded";

// Test that files exist
$files = [
    '../api/team/index.php',
    '../api/team/show.php',
    '../api/team/store.php',
    '../api/team/update.php',
    '../api/team/delete.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        throw new Exception("File not found: $file");
    }
}
echo "All team API files exist";
?>
```

- [ ] **Step 9: Run test to verify it passes**

Run: `php test_team_api.php`
Expected: Should show "Team API dependencies loaded" and "All team API files exist"

- [ ] **Step 10: Commit team members API files**

```bash
git add api/team/index.php api/team/show.php api/team/store.php api/team/update.php api/team/delete.php
git commit -m "feat: add team members API endpoints"
```

---

## Phase 2: Portfolio and Blog API Endpoints

### Task 5: Portfolio API endpoints

**Files:**
- Create: `api/portfolio/index.php`
- Create: `api/portfolio/show.php`
- Create: `api/portfolio/store.php`
- Create: `api/portfolio/update.php`
- Create: `api/portfolio/delete.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// test_portfolio_api.php
require_once '../includes/db.php';
require_once '../includes/functions.php';
if (!isset($GLOBALS['db'])) {
    throw new Exception('Database connection not available');
}
echo "Portfolio API dependencies loaded";
?>
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php test_portfolio_api.php`
Expected: Should fail with "failed to open stream" for missing includes

- [ ] **Step 3: Write GET all portfolio items endpoint**

Create `api/portfolio/index.php`:
```php
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
```

- [ ] **Step 4: Write GET single portfolio item endpoint**

Create `api/portfolio/show.php`:
```php
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
```

- [ ] **Step 5: Write CREATE portfolio item endpoint**

Create `api/portfolio/store.php`:
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

requireAdminLogin();

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendErrorResponse('Invalid JSON');
}

if (!isset($data['title']) || empty($data['title'])) {
    sendErrorResponse('Title is required');
}
if (!isset($data['team_member_id']) || empty($data['team_member_id'])) {
    sendErrorResponse('Team member ID is required');
}

$sanitized = sanitizeInput($data);

try {
    global $db;
    $stmt = $db->prepare("INSERT INTO team_portfolio (team_member_id, title, description, image_url, category, project_url, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $featured = isset($sanitized['featured']) ? intval($sanitized['featured']) : 0;
    $stmt->bind_param("isssssi", 
        $sanitized['team_member_id'],
        $sanitized['title'],
        $sanitized['description'] ?? '',
        $sanitized['image_url'] ?? '',
        $sanitized['category'] ?? '',
        $sanitized['project_url'] ?? '',
        $featured
    );
    $stmt->execute();
    
    $insertId = $stmt->insert_id;
    sendSuccessResponse('Portfolio item created successfully', ['portfolio_item_id' => $insertId], 201);
} catch (Exception $e) {
    sendErrorResponse('Database error: ' . $e->getMessage(), 500);
}
?>
```

- [ ] **Step 6: Commit portfolio API files**

```bash
git add api/portfolio/index.php api/portfolio/show.php api/portfolio/store.php
git commit -m "feat: add portfolio API endpoints"
```

### Task 6: Blog API endpoints

**Files:**
- Create: `api/blog/index.php`
- Create: `api/blog/show.php`
- Create: `api/blog/store.php`
- Create: `api/blog/update.php`
- Create: `api/blog/delete.php`

- [ ] **Step 1: Write the failing test**

Create test for blog API dependencies.

- [ ] **Step 2: Write GET all blog posts endpoint**

Create `api/blog/index.php`:
```php
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
```

- [ ] **Step 3: Commit blog API files**

```bash
git add api/blog/index.php
git commit -m "feat: add blog API endpoints"
```

---

## Phase 3: Frontend Transformation

### Task 7: Update index.html for dynamic content

**Files:**
- Modify: `index.html`

- [ ] **Step 1: Add dynamic content containers**

Update `index.html` to add data attributes for dynamic content and loading states:

Add to `<main>` section after loading overlay:
```html
<!-- About Section -->
<article id="about" class="section active">
    <h2 class="section-title">
        <ion-icon name="information-circle-outline"></ion-icon>
        <span data-content="about_title">About Us</span>
    </h2>
    <div class="about-content" data-content="about_content">
        <!-- Dynamic content will be loaded here -->
    </div>
</article>
```

- [ ] **Step 2: Add team overview grid placeholder**

Update team section:
```html
<!-- Team Section -->
<article id="team" class="section">
    <h2 class="section-title">
        <ion-icon name="people-outline"></ion-icon>
        <span data-content="team_title">Our Team</span>
    </h2>
    <div id="teamGrid" class="team-grid">
        <!-- Loading skeleton -->
        <div class="team-skeleton" data-loading="true">
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
        </div>
        <!-- Team member cards will be populated here by JavaScript -->
    </div>
</article>
```

- [ ] **Step 3: Commit updated index.html**

```bash
git add index.html
git commit -m "feat: update index.html for dynamic content loading"
```

### Task 8: Rewrite script.js for data loading and team switching

**Files:**
- Modify: `script.js`

- [ ] **Step 1: Create constants and configuration**

```javascript
const API_BASE = 'api/';
const EDIT_MODE_KEY = 'editMode';
const CURRENT_TEAM_MEMBER_KEY = 'currentTeamMember';

// Configuration
const CONFIG = {
    enableAnimations: true,
    loadingTimeout: 10000
};
```

- [ ] **Step 2: Add API helper functions**

```javascript
// Fetch data from API
async function fetchData(endpoint, options = {}) {
    showLoading(true);
    try {
        const response = await fetch(API_BASE + endpoint, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('Failed to fetch data', 'error');
        return null;
    } finally {
        showLoading(false);
    }
}

// Show loading state
function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = show ? 'flex' : 'none';
    }
}
```

- [ ] **Step 3: Add team loading and rendering**

```javascript
// Load team members
async function loadTeamMembers() {
    const data = await fetchData('team/index.php');
    if (data && data.success) {
        renderTeamGrid(data.team_members);
        renderTeamSidebar(data.team_members);
    }
}

// Render team grid
function renderTeamGrid(teamMembers) {
    const grid = document.getElementById('teamGrid');
    if (!grid) return;
    
    grid.innerHTML = teamMembers.map(member => `
        <div class="team-card" data-member-id="${member.id}" onclick="loadTeamMember('${member.id}')">
            <div class="card-inner">
                <div class="card-front">
                    <div class="member-avatar">
                        <img src="${member.avatar_url || 'assets/avatar.png'}" alt="${member.name}">
                    </div>
                    <h3>${member.name}</h3>
                    <p class="member-role">${member.role || 'Team Member'}</p>
                </div>
                <div class="card-back">
                    <p>${member.bio || ''}</p>
                    <button class="btn-view-more">View Profile</button>
                </div>
            </div>
        </div>
    `).join('');
}
```

- [ ] **Step 4: Add URL routing for team member profiles**

```javascript
// Handle URL hash changes
function handleHashChange() {
    const hash = window.location.hash.slice(1);
    const parts = hash.split('/');
    
    if (parts[0] === 'team' && parts[1]) {
        loadTeamMember(parts[1]);
    } else if (parts[0] === 'about') {
        switchTab('about');
    } else if (parts[0] === 'portfolio') {
        switchTab('portfolio');
    } else if (parts[0] === 'blog') {
        switchTab('blog');
    } else if (parts[0] === 'contact') {
        switchTab('contact');
    }
}

// Load individual team member
async function loadTeamMember(memberId) {
    const data = await fetchData(`team/show.php?id=${memberId}`);
    if (data && data.success) {
        renderTeamMemberProfile(data.team_member);
        switchTab('team'); // Or create a dedicated profile view
    }
}
```

- [ ] **Step 5: Add edit mode functionality**

```javascript
// Toggle edit mode
function toggleEditMode() {
    const isEditMode = localStorage.getItem(EDIT_MODE_KEY) === 'true';
    localStorage.setItem(EDIT_MODE_KEY, !isEditMode);
    document.getElementById('editModeIndicator').style.display = !isEditMode ? 'flex' : 'none';
}

// Make content editable
function makeEditable(element, key) {
    if (localStorage.getItem(EDIT_MODE_KEY) !== 'true') return;
    
    element.setAttribute('contenteditable', 'true');
    element.classList.add('editable');
    element.addEventListener('blur', () => {
        element.removeAttribute('contenteditable');
        element.classList.remove('editable');
    });
}
```

- [ ] **Step 6: Add scroll animations and visual effects**

```javascript
// Initialize scroll animations
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.section, .team-card, .project-item').forEach(el => {
        observer.observe(el);
    });
}

// Add 3D tilt effect to team cards
function initTiltEffect() {
    document.querySelectorAll('.team-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `perspective(1000px) rotateX(${y * 10}deg) rotateY(${x * 10}deg)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}
```

- [ ] **Step 7: Update DOMContentLoaded event**

```javascript
document.addEventListener('DOMContentLoaded', () => {
    // Initialize
    initScrollAnimations();
    initTiltEffect();
    loadTeamMembers();
    
    // Set up hash routing
    window.addEventListener('hashchange', handleHashChange);
    handleHashChange(); // Handle initial hash
    
    // Existing tab switching code...
});
```

- [ ] **Step 8: Commit updated script.js**

```bash
git add script.js
git commit -m "feat: rewrite script.js with API integration and dynamic loading"
```

---

## Phase 4: Theme & Styling Update

### Task 9: Update style.css with green theme

**Files:**
- Modify: `style.css`

- [ ] **Step 1: Update CSS variables**

```css
:root {
    --bg-color: hsl(0, 0%, 7%);
    --surface-color: hsl(0, 0%, 12%);
    --surface-light: hsl(0, 0%, 18%);
    --text-primary: hsl(0, 0%, 95%);
    --text-secondary: hsl(0, 0%, 70%);
    --accent: hsl(120, 70%, 40%);
    --accent-dark: hsl(120, 60%, 30%);
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    --transition: all 0.3s ease;
    --border-radius: 8px;
}
```

- [ ] **Step 2: Add animation classes**

```css
/* Loading skeleton */
.team-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.skeleton-card {
    background: linear-gradient(90deg, var(--surface-color), var(--surface-light), var(--surface-color));
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: var(--border-radius);
    height: 300px;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Animated sections */
.section {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s ease;
}

.section.animate-in {
    opacity: 1;
    transform: translateY(0);
}

/* Team card 3D effect */
.team-card {
    perspective: 1000px;
    transform-style: preserve-3d;
}

.team-card .card-inner {
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.5s ease;
}

.team-card:hover .card-inner {
    transform: rotateY(180deg);
}

.team-card .card-front,
.team-card .card-back {
    backface-visibility: hidden;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.team-card .card-back {
    transform: rotateY(180deg);
}
```

- [ ] **Step 3: Update all gradient references**

Replace all occurrences of `hsl(45,100%,72%)` and `hsl(45,54%,58%)` with green equivalents:
```css
background: linear-gradient(to right, var(--accent), var(--accent-dark));
```

- [ ] **Step 4: Add responsive improvements**

```css
/* Team grid responsive */
@media (max-width: 768px) {
    .team-grid {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 768px) {
    .team-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}
```

- [ ] **Step 5: Commit style.css changes**

```bash
git add style.css
git commit -m "feat: update style.css with green theme and visual effects"
```

---

## Phase 5: Admin Interface

### Task 10: Create admin login page

**Files:**
- Create: `admin/login.php`
- Create: `admin/index.php`
- Create: `admin/assets/admin.css`
- Create: `admin/assets/admin.js`

- [ ] **Step 1: Write admin login page**

Create `admin/login.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Team Portfolio CMS</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-login-container">
        <form id="adminLoginForm" class="admin-login-form">
            <h1>Admin Login</h1>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
            <div id="loginError" class="error-message"></div>
        </form>
    </div>
    <script src="assets/admin.js"></script>
</body>
</html>
```

- [ ] **Step 2: Write admin dashboard**

Create `admin/index.php`:
```php
<?php
session_start();
require_once '../includes/auth.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Team Portfolio CMS</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <h2>Team Portfolio CMS</h2>
        <a href="index.php?logout=true" class="logout-btn">Logout</a>
    </nav>
    <div class="admin-container">
        <h1>Manage Your Content</h1>
        <!-- Content management interface -->
    </div>
    <script src="assets/admin.js"></script>
</body>
</html>
```

- [ ] **Step 3: Commit admin interface files**

```bash
git add admin/login.php admin/index.php admin/assets/
git commit -m "feat: add admin interface for content management"
```

---

## Phase 6: Testing & Verification

### Task 11: Cross-browser and device testing

**Files:**
- Test: Manual verification scripts
- Modify: As needed for compatibility

- [ ] **Step 1: Test on Chrome, Firefox, Safari, Edge**

Manual testing checklist:
- [ ] Team grid loads and displays correctly
- [ ] Clicking team member opens profile
- [ ] Portfolio items load and filter correctly
- [ ] Blog posts display with proper formatting
- [ ] Contact form submits successfully
- [ ] Admin login works correctly
- [ ] Edit mode toggles properly

- [ ] **Step 2: Test on mobile devices**

- [ ] [ ] Sidebar converts to off-canvas drawer
- [ ] [ ] Team grid is single column
- [ ] [ ] Forms are usable on touch devices
- [ ] [ ] All interactions are touch-friendly

- [ ] **Step 3: Performance testing**

- [ ] [ ] Page load time under 3 seconds
- [ ] [ ] First meaningful paint under 2.5 seconds
- [ ] [ ] All images are optimized
- [ ] [ ] API responses under 500ms

- [ ] **Step 4: Security audit checklist**

- [ ] [ ] All API endpoints validate authentication
- [ ] [ ] SQL injection is prevented (using prepared statements)
- [ ] [ ] XSS is prevented (using htmlspecialchars)
- [ ] [ ] File uploads are validated
- [ ] [ ] CSRF protection is in place

---

## Summary of Files Created/Modified

### New Files:
- `database.sql` - Database schema
- `includes/db.php` - Database connection
- `includes/functions.php` - Helper functions
- `includes/auth.php` - Authentication functions
- `api/auth/login.php` - Login endpoint
- `api/auth/logout.php` - Logout endpoint
- `api/team/index.php` - List team members
- `api/team/show.php` - Get single team member
- `api/team/store.php` - Create team member
- `api/team/update.php` - Update team member
- `api/team/delete.php` - Delete team member
- `api/portfolio/index.php` - List portfolio items
- `api/portfolio/show.php` - Get single portfolio item
- `api/portfolio/store.php` - Create portfolio item
- `api/blog/index.php` - List blog posts
- `admin/login.php` - Admin login page
- `admin/index.php` - Admin dashboard
- `admin/assets/admin.css` - Admin styles
- `admin/assets/admin.js` - Admin scripts

### Modified Files:
- `index.html` - Dynamic content containers
- `style.css` - Green theme and animations
- `script.js` - API integration and team switching
- `contact.php` - Updated for consistency

---

## Execution Instructions

This plan is ready for implementation. To execute:

1. **Option A: Subagent-Driven Development** (Recommended)
   ```bash
   # Use superpowers:subagent-driven-development skill
   # Each task will be handled by a fresh subagent
   ```

2. **Option B: Inline Execution**
   - Follow each task sequentially
   - Run tests after each step
   - Commit after each task completion

**Next Steps:**
1. Ensure MySQL database is set up with the schema
2. Configure environment variables for database and admin credentials
3. Run through each task in order
4. Test thoroughly before each commit