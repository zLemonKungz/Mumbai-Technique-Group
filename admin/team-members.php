<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require admin login
requireAdminLogin();

// Handle form submissions for create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle delete request
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        try {
            global $db;
            // First check if team member exists
            $stmt = $db->prepare("SELECT id FROM team_members WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $_SESSION['error'] = 'Team member not found';
            } else {
                // Delete team member
                $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $_SESSION['success'] = 'Team member deleted successfully';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }

        header('Location: team-members.php');
        exit;
    }
}

// Get team members for display
$teamMembers = [];
try {
    global $db;
    $stmt = $db->prepare("SELECT id, name, role, bio, avatar_url, email, created_at FROM team_members ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $teamMembers[] = $row;
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
    <title>Team Members - PixelTest Team Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>PixelTest Team</h>
                <p>Admin Panel</p>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="nav-item">
                    <ion-icon name="home-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
                <a href="team-members.php" class="nav-item active">
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
                <h1>Team Members Management</h1>
                <div class="admin-header-actions">
                    <a href="team-members-create.php" class="btn-primary">
                        <ion-icon name="add-outline"></ion-icon>
                        Add New Member
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
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teamMembers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No team members found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teamMembers as $member): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($member['avatar_url'] ?? 'https://via.placeholder.com/50'); ?>"
                                                 alt="<?php echo htmlspecialchars($member['name']); ?>"
                                                 class="table-avatar">
                                        </td>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['role'] ?? 'Team Member'); ?></td>
                                        <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                                        <td class="table-actions">
                                            <a href="team-members-edit.php?id=<?php echo $member['id']; ?>" class="action-btn edit-btn">
                                                <ion-icon name="create-outline"></ion-icon>
                                                Edit
                                            </a>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this team member?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
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
</body>
</html>