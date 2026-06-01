<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Get team member ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid team member ID';
    header('Location: team-members.php');
    exit;
}

// Get team member data
$teamMember = null;
try {
    global $db;
    $stmt = $db->prepare("SELECT id, name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Team member not found';
        header('Location: team-members.php');
        exit;
    }

    $teamMember = $result->fetch_assoc();
    // Parse JSON fields
    $teamMember['social_links'] = json_decode($teamMember['social_links'], true) ?? [];
    $teamMember['skills'] = json_decode($teamMember['skills'], true) ?? [];
    $teamMember['portfolio_items'] = json_decode($teamMember['portfolio_items'], true) ?? [];
    $teamMember['blog_posts'] = json_decode($teamMember['blog_posts'], true) ?? [];
} catch (Exception $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: team-members.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $avatar_url = isset($_POST['avatar_url']) ? trim($_POST['avatar_url']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Handle JSON fields
    $social_links = isset($_POST['social_links']) ? json_encode([
        'twitter' => isset($_POST['twitter']) ? trim($_POST['twitter']) : '',
        'linkedin' => isset($_POST['linkedin']) ? trim($_POST['linkedin']) : '',
        'github' => isset($_POST['github']) ? trim($_POST['github']) : '',
        'instagram' => isset($_POST['instagram']) ? trim($_POST['instagram']) : ''
    ]) : '{}';

    $skills = isset($_POST['skills']) ? json_encode([
        [
            'name' => isset($_POST['skill_1_name']) ? trim($_POST['skill_1_name']) : '',
            'level' => isset($_POST['skill_1_level']) ? intval($_POST['skill_1_level']) : 0
        ],
        [
            'name' => isset($_POST['skill_2_name']) ? trim($_POST['skill_2_name']) : '',
            'level' => isset($_POST['skill_2_level']) ? intval($_POST['skill_2_level']) : 0
        ],
        [
            'name' => isset($_POST['skill_3_name']) ? trim($_POST['skill_3_name']) : '',
            'level' => isset($_POST['skill_3_level']) ? intval($_POST['skill_3_level']) : 0
        ]
    ]) : '[]';

    $portfolio_items = isset($_POST['portfolio_items']) ? json_encode(array_filter(array_map('intval', explode(',', $_POST['portfolio_items'])))) : '[]';
    $blog_posts = isset($_POST['blog_posts']) ? json_encode(array_filter(array_map('intval', explode(',', $_POST['blog_posts'])))) : '[]';

    // Validate required fields
    if (empty($name)) {
        $error = 'Team member name is required';
    } else {
        try {
            global $db;
            $stmt = $db->prepare("UPDATE team_members SET name = ?, role = ?, bio = ?, avatar_url = ?, email = ?, social_links = ?, skills = ?, portfolio_items = ?, blog_posts = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("sssssssssi", $name, $role, $bio, $avatar_url, $email, $social_links, $skills, $portfolio_items, $blog_posts, $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = 'Team member updated successfully';
                header('Location: team-members.php');
                exit;
            } else {
                $error = 'No changes made or failed to update team member';
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
    <title>Edit Team Member - PixelTest Team Admin</title>
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
                <a href="team-members-create.php" class="nav-item">
                    <ion-icon name="add-outline"></ion-icon>
                    <span>Add New Member</span>
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
                <h1>Edit Team Member</h1>
                <div class="admin-header-actions">
                    <a href="team-members.php" class="btn-secondary">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        Back to Team Members
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
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($teamMember['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($teamMember['role']); ?>" placeholder="Enter role (e.g., Designer, Developer)">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Enter team member bio"><?php echo htmlspecialchars($teamMember['bio']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="avatar_url">Avatar URL</label>
                        <input type="url" id="avatar_url" name="avatar_url" value="<?php echo htmlspecialchars($teamMember['avatar_url']); ?>" placeholder="Enter avatar image URL">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teamMember['email']); ?>" placeholder="Enter email address">
                    </div>

                    <fieldset class="form-group">
                        <legend>Social Links</legend>
                        <div class="social-links-grid">
                            <div>
                                <label for="twitter">Twitter</label>
                                <input type="url" id="twitter" name="twitter" value="<?php echo htmlspecialchars($teamMember['social_links']['twitter'] ?? ''); ?>" placeholder="Twitter handle or URL">
                            </div>
                            <div>
                                <label for="linkedin">LinkedIn</label>
                                <input type="url" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($teamMember['social_links']['linkedin'] ?? ''); ?>" placeholder="LinkedIn profile URL">
                            </div>
                            <div>
                                <label for="github">GitHub</label>
                                <input type="url" id="github" name="github" value="<?php echo htmlspecialchars($teamMember['social_links']['github'] ?? ''); ?>" placeholder="GitHub profile URL">
                            </div>
                            <div>
                                <label for="instagram">Instagram</label>
                                <input type="url" id="instagram" name="instagram" value="<?php echo htmlspecialchars($teamMember['social_links']['instagram'] ?? ''); ?>" placeholder="Instagram profile URL">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-group">
                        <legend>Skills (up to 3)</legend>
                        <div class="skills-grid">
                            <div class="skill-item">
                                <div class="form-group">
                                    <label for="skill_1_name">Skill 1 Name</label>
                                    <input type="text" id="skill_1_name" name="skill_1_name" value="<?php echo htmlspecialchars($teamMember['skills'][0]['name'] ?? ''); ?>" placeholder="e.g., JavaScript">
                                </div>
                                <div class="form-group">
                                    <label for="skill_1_level">Skill Level (%)</label>
                                    <input type="number" id="skill_1_level" name="skill_1_level" min="0" max="100" value="<?php echo htmlspecialchars($teamMember['skills'][0]['level'] ?? 80); ?>">
                                </div>
                            </div>
                            <div class="skill-item">
                                <div class="form-group">
                                    <label for="skill_2_name">Skill 2 Name</label>
                                    <input type="text" id="skill_2_name" name="skill_2_name" value="<?php echo htmlspecialchars($teamMember['skills'][1]['name'] ?? ''); ?>" placeholder="e.g., React">
                                </div>
                                <div class="form-group">
                                    <label for="skill_2_level">Skill Level (%)</label>
                                    <input type="number" id="skill_2_level" name="skill_2_level" min="0" max="100" value="<?php echo htmlspecialchars($teamMember['skills'][1]['level'] ?? 70); ?>">
                                </div>
                            </div>
                            <div class="skill-item">
                                <div class="form-group">
                                    <label for="skill_3_name">Skill 3 Name</label>
                                    <input type="text" id="skill_3_name" name="skill_3_name" value="<?php echo htmlspecialchars($teamMember['skills'][2]['name'] ?? ''); ?>" placeholder="e.g., Node.js">
                                </div>
                                <div class="form-group">
                                    <label for="skill_3_level">Skill Level (%)</label>
                                    <input type="number" id="skill_3_level" name="skill_3_level" min="0" max="100" value="<?php echo htmlspecialchars($teamMember['skills'][2]['level'] ?? 60); ?>">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-group">
                        <legend>Related Content (IDs)</legend>
                        <div class="form-group">
                            <label for="portfolio_items">Portfolio Item IDs (comma-separated)</label>
                            <input type="text" id="portfolio_items" name="portfolio_items" value="<?php echo htmlspecialchars(implode(',', array_filter($teamMember['portfolio_items']))); ?>" placeholder="e.g., 1,2,3">
                            <small>Enter existing portfolio item IDs to associate with this team member</small>
                        </div>
                        <div class="form-group">
                            <label for="blog_posts">Blog Post IDs (comma-separated)</label>
                            <input type="text" id="blog_posts" name="blog_posts" value="<?php echo htmlspecialchars(implode(',', array_filter($teamMember['blog_posts']))); ?>" placeholder="e.g., 1,2,3">
                            <small>Enter existing blog post IDs to associate with this team member</small>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn-primary btn-block">
                        <ion-icon name="create-outline"></ion-icon>
                        Update Team Member
                    </button>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
</body>
</html>