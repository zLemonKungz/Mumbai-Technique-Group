<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Handle form submissions for delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle delete request
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        try {
            global $db;
            // First check if portfolio item exists
            $stmt = $db->prepare("SELECT id FROM team_portfolio WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $_SESSION['error'] = 'Portfolio item not found';
            } else {
                // Delete portfolio item
                $stmt = $db->prepare("DELETE FROM team_portfolio WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $_SESSION['success'] = 'Portfolio item deleted successfully';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }

        header('Location: portfolio.php');
        exit;
    }
}

// Get filter parameters
$teamMemberFilter = isset($_GET['team_member']) ? intval($_GET['team_member']) : null;
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$featuredFilter = isset($_GET['featured']) ? intval($_GET['featured']) : null;

// Get team members for filter dropdown
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

// Get portfolio items with filters
$portfolioItems = [];
try {
    global $db;

    $sql = "SELECT p.*, tm.name as member_name FROM team_portfolio p LEFT JOIN team_members tm ON p.team_member_id = tm.id WHERE 1=1";
    $params = [];
    $types = '';

    if ($teamMemberFilter) {
        $sql .= " AND p.team_member_id = ?";
        $params[] = $teamMemberFilter;
        $types .= 'i';
    }
    if ($categoryFilter) {
        $sql .= " AND p.category = ?";
        $params[] = $categoryFilter;
        $types .= 's';
    }
    if ($featuredFilter !== null) {
        $sql .= " AND p.featured = ?";
        $params[] = $featuredFilter;
        $types .= 'i';
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $portfolioItems[] = $row;
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
    <title>Portfolio - PixelTest Team Admin</title>
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
                <a href="portfolio.php" class="nav-item active">
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
                <h1>Portfolio Management</h1>
                <div class="admin-header-actions">
                    <a href="portfolio-create.php" class="btn-primary">
                        <ion-icon name="add-outline"></ion-icon>
                        Add New Project
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
                <!-- Filters -->
                <div class="filters-panel">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="team_member_filter">Team Member</label>
                            <select id="team_member_filter" name="team_member">
                                <option value="">All Team Members</option>
                                <?php foreach ($teamMembers as $member): ?>
                                    <option value="<?php echo $member['id']; ?>" <?php echo ($teamMemberFilter == $member['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="category_filter">Category</label>
                            <select id="category_filter" name="category">
                                <option value="">All Categories</option>
                                <option value="web" <?php echo ($categoryFilter == 'web') ? 'selected' : ''; ?>>Web Design</option>
                                <option value="app" <?php echo ($categoryFilter == 'app') ? 'selected' : ''; ?>>Mobile Apps</option>
                                <option value="branding" <?php echo ($categoryFilter == 'branding') ? 'selected' : ''; ?>>Branding</option>
                                <option value="ecommerce" <?php echo ($categoryFilter == 'ecommerce') ? 'selected' : ''; ?>>E-commerce</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="featured_filter">Featured</label>
                            <select id="featured_filter" name="featured">
                                <option value="">All</option>
                                <option value="1" <?php echo ($featuredFilter == 1) ? 'selected' : ''; ?>>Featured</option>
                                <option value="0" <?php echo ($featuredFilter == 0) ? 'selected' : ''; ?>>Not Featured</option>
                            </select>
                        </div>

                        <button id="applyFilters" class="btn-primary">
                            <ion-icon name="funnel-outline"></ion-icon>
                            Apply Filters
                        </button>
                        <a href="portfolio.php" class="btn-secondary">
                            <ion-icon name="refresh-outline"></ion-icon>
                            Reset
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Title</th>
                                <th>Team Member</th>
                                <th>Category</th>
                                <th>Featured</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($portfolioItems)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No portfolio items found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($portfolioItems as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/50'); ?>"
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="table-avatar">
                                        </td>
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td><?php echo htmlspecialchars($item['member_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo htmlspecialchars($item['category']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($item['category'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($item['featured']): ?>
                                                <span class="badge badge-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                        <td class="table-actions">
                                            <a href="portfolio-edit.php?id=<?php echo $item['id']; ?>" class="action-btn edit-btn">
                                                <ion-icon name="create-outline"></ion-icon>
                                                Edit
                                            </a>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this portfolio item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="action-btn delete-btn">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/admin.js"></script>
    <script>
        // Apply filters when button is clicked
        document.getElementById('applyFilters').addEventListener('click', function() {
            const teamMember = document.getElementById('team_member_filter').value;
            const category = document.getElementById('category_filter').value;
            const featured = document.getElementById('featured_filter').value;

            let url = 'portfolio.php';
            const params = [];

            if (teamMember) params.push('team_member=' + teamMember);
            if (category) params.push('category=' + category);
            if (featured !== '') params.push('featured=' + featured);

            if (params.length > 0) {
                url += '?' + params.join('&');
            }

            window.location.href = url;
        });

        // Also apply filters on change (optional)
        document.getElementById('team_member_filter').addEventListener('change', function() {
            document.getElementById('applyFilters').click();
        });
        document.getElementById('category_filter').addEventListener('change', function() {
            document.getElementById('applyFilters').click();
        });
        document.getElementById('featured_filter').addEventListener('change', function() {
            document.getElementById('applyFilters').click();
        });
    </script>
</body>
</html>