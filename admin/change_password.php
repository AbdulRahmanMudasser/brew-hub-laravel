<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['message'] = "Invalid CSRF token.";
        $_SESSION['message_type'] = 'error';
        header("Location: change_password.php");
        exit;
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password)) $errors['current_password'] = "Current password is required.";
    if (empty($new_password)) $errors['new_password'] = "New password is required.";
    if (empty($confirm_password)) $errors['confirm_password'] = "Confirm password is required.";
    if ($new_password !== $confirm_password) $errors['confirm_password'] = "New passwords do not match.";
    if ($new_password && strlen($new_password) < 8) $errors['new_password'] = "New password must be at least 8 characters.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
            $_SESSION['message'] = "Password changed successfully.";
            $_SESSION['message_type'] = 'success';
            header("Location: change_password.php");
            exit;
        } else {
            $errors['current_password'] = "Current password is incorrect.";
        }
    }
}

// Fetch admin name
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
$admin_name = $admin['name'] ?? $admin['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Bean & Brew</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Arial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                
                <h2>Bean & Brew</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_menu.php">Manage Menu</a></li>
                    <li><a href="manage_gallery.php">Manage Gallery</a></li>
                    <li><a href="change_password.php" class="active">Change Password</a></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h1>Change Password</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                </div>
            </header>
            <?php display_message(); ?>
            <section class="form-section">
                <h2>Update Password</h2>
                <form method="POST" id="passwordForm" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <?php if (isset($errors['current_password'])): ?>
                            <span class="error"><?php echo $errors['current_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <?php if (isset($errors['new_password'])): ?>
                            <span class="error"><?php echo $errors['new_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="error"><?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="Change Password">
                        <button type="reset" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script>
        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            let valid = true;
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            document.querySelectorAll('.error').forEach(el => el.remove());

            if (!currentPassword) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Current password is required.';
                document.getElementById('current_password').after(error);
            }
            if (!newPassword) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'New password is required.';
                document.getElementById('new_password').after(error);
            } else if (newPassword.length < 8) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'New password must be at least 8 characters.';
                document.getElementById('new_password').after(error);
            }
            if (!confirmPassword) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Confirm password is required.';
                document.getElementById('confirm_password').after(error);
            } else if (newPassword !== confirmPassword) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'New passwords do not match.';
                document.getElementById('confirm_password').after(error);
            }

            if (!valid) {
                e.preventDefault();
            }
        });

        // Sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.createElement('button');
            toggle.className = 'sidebar-toggle';
            toggle.innerHTML = '☰';
            document.querySelector('.admin-header').prepend(toggle);

            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        });

        // Logout confirmation
        document.querySelectorAll('.logout-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>