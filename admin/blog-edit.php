<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Get blog post ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid blog post ID';
    header('Location: blog.php');
    exit;
}

// Get blog post data
$blogPost = null;
try {
    global $db;
    $stmt = $db->prepare("SELECT b.*, tm.name as member_name FROM team_blog b LEFT JOIN team_members tm ON b.team_member_id = tm.id WHERE b.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Blog post not found';
        header('Location: blog.php');
        exit;
    }

    $blogPost = $result->fetch_assoc();
} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: blog.php');
    exit;
}

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
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Validate required fields
    if (!$team_member_id) {
        $error = 'Team member is required';
    } elseif (empty($title)) {
        $error = 'Post title is required';
    } elseif (empty($content)) {
        $error = 'Post content is required';
    } else {
        try {
            global $db;
            $stmt = $db->prepare("UPDATE team_blog SET team_member_id = ?, title = ?, content = ?, excerpt = ?, image_url = ?, category = ?, featured = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("isssssii", $team_member_id, $title, $content, $excerpt, $image_url, $category, $featured, $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = 'Blog post updated successfully';
                header('Location: blog.php');
                exit;
            } else {
                $error = 'No changes made or failed to update blog post';
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
    <title>Edit Blog Post - PixelTest Team Admin</title>
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
                <h1>Edit Blog Post</h1>
                <div class="admin-header-actions">
                    <a href="blog.php" class="btn-secondary">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        Back to Blog
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
                                <option value="<?php echo $member['id']; ?>" <?php echo ($blogPost['team_member_id'] == $member['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($member['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($blogPost['title']); ?>" placeholder="Enter post title">
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" rows="8" required placeholder="Enter post content"><?php echo htmlspecialchars($blogPost['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt (optional)</label>
                        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Enter post excerpt (will be auto-generated if empty)"><?php echo htmlspecialchars($blogPost['excerpt']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($blogPost['image_url']); ?>" placeholder="Enter post image URL">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="web" <?php echo ($blogPost['category'] == 'web') ? 'selected' : ''; ?>>Web Design</option>
                            <option value="app" <?php echo ($blogPost['category'] == 'app') ? 'selected' : ''; ?>>Mobile Apps</option>
                            <option value="branding" <?php echo ($blogPost['category'] == 'branding') ? 'selected' : ''; ?>>Branding</option>
                            <option value="ecommerce" <?php echo ($blogPost['category'] == 'ecommerce') ? 'selected' : ''; ?>>E-commerce</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="featured">
                            <input type="checkbox" id="featured" name="featured" value="1" <?php echo ($blogPost['featured']) ? 'checked' : ''; ?>>
                            Mark as Featured
                        </label>
                    </div>

                    <button type="submit" class="btn-primary btn-block">
                        <ion-icon name="create-outline"></ion-icon>
                        Update Blog Post
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>