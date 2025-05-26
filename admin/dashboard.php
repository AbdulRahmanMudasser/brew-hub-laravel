<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

// Fetch admin name
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
$admin_name = $admin['name'] ?? $admin['username'];

// Fetch stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM menu_items");
$menu_count = $stmt->fetch()['count'];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_images");
$gallery_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bean & Brew</title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="manage_menu.php">Manage Menu</a></li>
                    <li><a href="manage_gallery.php">Manage Gallery</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                </div>
            </header>
            <?php display_message(); ?>
            <section class="dashboard-section">
                <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Menu Items</h3>
                        <p><?php echo $menu_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Gallery Images</h3>
                        <p><?php echo $gallery_count; ?></p>
                    </div>
                </div>
                <div class="action-grid">
                    <a href="manage_menu.php" class="action-card">
                        <h3>Manage Menu</h3>
                        <p>Add, edit, or delete menu items.</p>
                    </a>
                    <a href="manage_gallery.php" class="action-card">
                        <h3>Manage Gallery</h3>
                        <p>Upload or update gallery images.</p>
                    </a>
                    <a href="change_password.php" class="action-card">
                        <h3>Change Password</h3>
                        <p>Update your account password.</p>
                    </a>
                </div>
            </section>
        </main>
    </div>
    <script>
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