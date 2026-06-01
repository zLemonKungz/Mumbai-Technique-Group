<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Handle form submission for updating settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $settings = [];

    // Site settings
    $settings['site_title'] = isset($_POST['site_title']) ? trim($_POST['site_title']) : '';
    $settings['site_description'] = isset($_POST['site_description']) ? trim($_POST['site_description']) : '';

    // About section
    $settings['about_title'] = isset($_POST['about_title']) ? trim($_POST['about_title']) : '';
    $settings['about_content'] = isset($_POST['about_content']) ? trim($_POST['about_content']) : '';

    // Team section title
    $settings['team_title'] = isset($_POST['team_title']) ? trim($_POST['team_title']) : '';

    try {
        global $db;

        // Update each setting
        foreach ($settings as $key => $value) {
            // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert
            $stmt = $db->prepare("INSERT INTO team_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }

        $_SESSION['success'] = 'Settings updated successfully';
        header('Location: settings.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
}

// Get current settings for display
$currentSettings = [];
try {
    global $db;
    $stmt = $db->prepare("SELECT `key`, `value` FROM team_settings");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $currentSettings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

// Get flash messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// Clear flash messages
unset($_SESSION['success']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PixelTest Team Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>PixelTest Team</h2>
                <p>Admin Panel</p>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="nav-item">
                    <ion-icon name="home-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
                <a href="team-members.php" class="nav-item">
                    <ion-icon name="people-outline"></ion-icon>
                    <span>Team Members</span>
                </a>
                <a href="portfolio.php" class="nav-item">
                    <ion-icon name="folder-open-outline"></ion-icon>
                    <span>Portfolio</span>
                </a>
                <a href="blog.php" class="nav-item">
                    <ion-icon name="book-outline"></ion-icon>
                    <span>Blog</span>
                </a>
                <a href="settings.php" class="nav-item active">
                    <ion-icon name="settings-outline"></ion-icon>
                    <span>Settings</span>
                </a>
            </nav>
            <div class="admin-sidebar-footer">
                <a href="index.php?action=logout" class="nav-item logout">
                    <ion-icon name="log-out-outline"></ion-icon>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Site Settings</h1>
                <div class="admin-header-actions">
                    <a href="index.php" class="btn-secondary">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        Back to Dashboard
                    </a>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <ion-icon name="checkmark-circle-outline"></ion-icon>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="admin-content">
                <form method="POST" action="">
                    <!-- Site Settings -->
                    <fieldset class="form-group">
                        <legend>Site Settings</legend>
                        <div class="form-group">
                            <label for="site_title">Site Title</label>
                            <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($currentSettings['site_title'] ?? 'PixelTest Team | Creative Agency'); ?>" placeholder="Enter site title (appears in browser tab)">
                        </div>

                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" rows="3" placeholder="Enter site description"><?php echo htmlspecialchars($currentSettings['site_description'] ?? 'Creative agency specializing in web design, mobile apps, branding, and e-commerce solutions'); ?></textarea>
                        </div>
                    </fieldset>

                    <!-- About Section -->
                    <fieldset class="form-group">
                        <legend>About Section</legend>
                        <div class="form-group">
                            <label for="about_title">About Section Title</label>
                            <input type="text" id="about_title" name="about_title" value="<?php echo htmlspecialchars($currentSettings['about_title'] ?? 'About Us'); ?>" placeholder="Enter about section title">
                        </div>

                        <div class="form-group">
                            <label for="about_content">About Section Content</label>
                            <textarea id="about_content" name="about_content" rows="8" placeholder="Enter about section content"><?php echo htmlspecialchars($currentSettings['about_content'] ?? '<p>We are a creative team passionate about delivering exceptional digital experiences. Our multidisciplinary approach combines thoughtful design, robust development, and strategic thinking to create solutions that engage users and drive business results.</p><p>With years of experience across various industries, we pride ourselves on our ability to understand complex challenges and transform them into elegant, functional solutions that exceed expectations.</p>'); ?></textarea>
                        </div>
                    </fieldset>

                    <!-- Team Section -->
                    <fieldset class="form-group">
                        <legend>Team Section</legend>
                        <div class="form-group">
                            <label for="team_title">Team Section Title</label>
                            <input type="text" id="team_title" name="team_title" value="<?php echo htmlspecialchars($currentSettings['team_title'] ?? 'Our Team'); ?>" placeholder="Enter team section title">
                        </div>
                    </fieldset>

                    <button type="submit" class="btn-primary btn-block">
                        <ion-icon name="create-outline"></ion-icon>
                        Save Settings
                    </button>
                </form>

                <div class="mt-4">
                    <h2>Database Information</h2>
                    <p><strong>Note:</strong> In a production environment, you would want to:</p>
                    <ul>
                        <li>Use environment variables for database credentials</li>
                        <li>Hash passwords instead of storing them in plain text</li>
                        <li>Implement proper input validation and sanitization</li>
                        <li>Add rate limiting and additional security measures</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>