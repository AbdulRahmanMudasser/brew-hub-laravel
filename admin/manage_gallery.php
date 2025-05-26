<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

// Initialize errors array
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['message'] = "Invalid CSRF token.";
        $_SESSION['message_type'] = 'error';
        header("Location: manage_gallery.php");
        exit;
    }

    $action = $_POST['action'] ?? '';
    $caption = sanitize_input($_POST['caption'] ?? '');
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

    if ($action === 'add' || $action === 'update') {
        $filename = '';
        if ($action === 'add' && empty($_FILES['image']['name'])) {
            $errors['image'] = "Image is required.";
        } elseif (!empty($_FILES['image']['name'])) {
            $image_errors = validate_image_upload($_FILES['image']);
            if (empty($image_errors)) {
                $filename = time() . '_' . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $filename)) {
                    $errors['image'] = "Failed to upload image.";
                }
            } else {
                $errors['image'] = implode(" ", $image_errors);
            }
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO gallery_images (filename, caption) VALUES (?, ?)");
                $stmt->execute([$filename, $caption]);
                $_SESSION['message'] = "Image uploaded successfully.";
                $_SESSION['message_type'] = 'success';
            } elseif ($action === 'update' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE gallery_images SET caption = ?, filename = IF(? != '', ?, filename) WHERE id = ?");
                $stmt->execute([$caption, $filename, $filename, $id]);
                $_SESSION['message'] = "Image updated successfully.";
                $_SESSION['message_type'] = 'success';
            }
            header("Location: manage_gallery.php");
            exit;
        }
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("SELECT filename FROM gallery_images WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        if ($image) {
            unlink(UPLOAD_PATH . $image['filename']);
            $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = "Image deleted successfully.";
            $_SESSION['message_type'] = 'success';
        }
        header("Location: manage_gallery.php");
        exit;
    }
}

// Fetch gallery images
$stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll();

// Fetch item for editing
$edit_image = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_image = $stmt->fetch();
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
    <title>Manage Gallery - Bean & Brew</title>
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
                    <li><a href="manage_gallery.php" class="active">Manage Gallery</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h1>Manage Gallery</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                </div>
            </header>
            <?php display_message(); ?>
            <section class="form-section">
                <h2><?php echo $edit_image ? 'Edit Image' : 'Upload Image'; ?></h2>
                <form method="POST" enctype="multipart/form-data" id="galleryForm" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="<?php echo $edit_image ? 'update' : 'add'; ?>">
                    <?php if ($edit_image): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_image['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" accept="image/*" <?php echo $edit_image ? '' : 'required'; ?>>
                        <div id="image-preview" class="image-preview">
                            <?php if ($edit_image && $edit_image['filename']): ?>
                                <img src="../<?php echo UPLOAD_DIR . $edit_image['filename']; ?>" alt="Current Image">
                            <?php endif; ?>
                        </div>
                        <?php if (isset($errors['image'])): ?>
                            <span class="error"><?php echo $errors['image']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="caption">Caption</label>
                        <input type="text" id="caption" name="caption" value="<?php echo htmlspecialchars($edit_image['caption'] ?? ''); ?>">
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="<?php echo $edit_image ? 'Update Image' : 'Upload Image'; ?>">
                        <button type="reset" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </section>
            <section class="table-section">
                <h2>Gallery Images</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Caption</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($images as $image): ?>
                                <tr>
                                    <td><img src="../<?php echo UPLOAD_DIR . $image['filename']; ?>" alt="<?php echo htmlspecialchars($image['caption'] ?? 'Gallery Image'); ?>" width="100"></td>
                                    <td><?php echo htmlspecialchars($image['caption'] ?? 'No caption'); ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $image['id']; ?>" class="action-btn edit-btn">Edit</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                                            <input type="submit" value="Delete" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this image?');">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            if (e.target.files[0]) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(e.target.files[0]);
                img.alt = 'Image Preview';
                preview.appendChild(img);
            }
        });

        // Form validation
        document.getElementById('galleryForm').addEventListener('submit', function(e) {
            let valid = true;
            const image = document.getElementById('image').files[0];
            const isAdd = document.querySelector('input[name="action"]').value === 'add';

            document.querySelectorAll('.error').forEach(el => el.remove());

            if (isAdd && !image) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Image is required.';
                document.getElementById('image').after(error);
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