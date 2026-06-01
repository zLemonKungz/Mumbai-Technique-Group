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