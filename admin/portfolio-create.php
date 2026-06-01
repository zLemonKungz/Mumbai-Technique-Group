<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Get team members for dropdown
$teamMembers = [];
try {
    global $db;
    $stmt = $db->prepare("SELECT id, name FROM team_members ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $teamMembers[] = $row;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $team_member_id = isset($_POST['team_member_id']) ? intval($_POST['team_member_id']) : null;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $project_url = isset($_POST['project_url']) ? trim($_POST['project_url']) : '';
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Validate required fields
    if (!$team_member_id) {
        $error = 'Team member is required';
    } elseif (empty($title)) {
        $error = 'Project title is required';
    } else {
        try {
            global $db;
            $stmt = $db->prepare("INSERT INTO team_portfolio (team_member_id, title, description, image_url, category, project_url, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssi", $team_member_id, $title, $description, $image_url, $category, $project_url, $featured);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = 'Portfolio item created successfully';
                header('Location: portfolio.php');
                exit;
            } else {
                $error = 'Failed to create portfolio item';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Portfolio Item - PixelTest Team Admin</title>
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
                <h1>Add New Portfolio Item</h1>
                <div class="admin-header-actions">
                    <a href="portfolio.php" class="btn-secondary">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        Back to Portfolio
                    </a>
                </div>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="admin-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="team_member_id">Team Member *</label>
                        <select id="team_member_id" name="team_member_id" required>
                            <option value="">Select Team Member</option>
                            <?php foreach ($teamMembers as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required placeholder="Enter project title">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Enter project description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" placeholder="Enter project image URL">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="web">Web Design</option>
                            <option value="app">Mobile Apps</option>
                            <option value="branding">Branding</option>
                            <option value="ecommerce">E-commerce</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="project_url">Project URL</label>
                        <input type="url" id="project_url" name="project_url" placeholder="Enter project live URL (optional)">
                    </div>

                    <div class="form-group">
                        <label for="featured">
                            <input type="checkbox" id="featured" name="featured" value="1">
                            Mark as Featured
                        </label>
                    </div>

                    <button type="submit" class="btn-primary btn-block">
                        <ion-icon name="add-outline"></ion-icon>
                        Create Portfolio Item
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>