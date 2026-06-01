<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } elseif (adminLogin($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PixelTest Team</title>
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>PixelTest Team Admin</h2>
                <p>Creative Agency Management</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-primary btn-block">
                    <ion-icon name="log-in-outline"></ion-icon>
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> PixelTest Team. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>