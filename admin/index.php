<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    adminLogout();
    header('Location: login.php');
    exit;
}

// Get statistics for dashboard
$stats = [
    'team_members' => 0,
    'portfolio_items' => 0,
    'blog_posts' => 0
];

try {
    global $db;

    // Count team members
    $stmt = $db->query("SELECT COUNT(*) as count FROM team_members");
    $result = $stmt->fetch_assoc();
    $stats['team_members'] = (int)$result['count'];

    // Count portfolio items
    $stmt = $db->query("SELECT COUNT(*) as count FROM team_portfolio");
    $result = $stmt->fetch_assoc();
    $stats['portfolio_items'] = (int)$result['count'];

    // Count blog posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM team_blog");
    $result = $stmt->fetch_assoc();
    $stats['blog_posts'] = (int)$result['count'];

} catch (Exception $e) {
    // In case of error, stats remain 0
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PixelTest Team</title>
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
                <a href="index.php" class="nav-item active">
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
                <a href="settings.php" class="nav-item">
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
                <h1>Dashboard</h1>
                <div class="admin-header-actions">
                    <span class="admin-user">
                        <ion-icon name="person-circle"></ion-icon>
                        <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                    </span>
                </div>
            </header>

            <div class="admin-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <ion-icon name="people"></ion-icon>
                        </div>
                        <div class="stat-info">
                            <h3>Team Members</h3>
                            <p><?php echo number_format($stats['team_members']); ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <ion-icon name="folder"></ion-icon>
                        </div>
                        <div class="stat-info">
                            <h3>Portfolio Items</h3>
                            <p><?php echo number_format($stats['portfolio_items']); ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <ion-icon name="book"></ion-icon>
                        </div>
                        <div class="stat-info">
                            <h3>Blog Posts</h3>
                            <p><?php echo number_format($stats['blog_posts']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Just now</td>
                                    <td><span class="activity-tag activity-login">Login</span></td>
                                    <td><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?> logged in</td>
                                </tr>
                                <!-- In a real app, this would come from an activity log -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>