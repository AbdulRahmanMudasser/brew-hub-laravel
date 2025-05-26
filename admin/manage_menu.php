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
        header("Location: manage_menu.php");
        exit;
    }

    $action = $_POST['action'] ?? '';
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

    if ($action === 'add' || $action === 'update') {
        if (empty($name)) $errors['name'] = "Name is required.";
        if (empty($description)) $errors['description'] = "Description is required.";
        if ($price === false || $price <= 0) $errors['price'] = "Valid price is required.";

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $image_errors = validate_image_upload($_FILES['image']);
            if (empty($image_errors)) {
                $image = time() . '_' . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $image)) {
                    $errors['image'] = "Failed to upload image.";
                }
            } else {
                $errors['image'] = implode(" ", $image_errors);
            }
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $image]);
                $_SESSION['message'] = "Menu item added successfully.";
                $_SESSION['message_type'] = 'success';
            } elseif ($action === 'update' && $id > 0) {
                $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, image = IF(? != '', ?, image) WHERE id = ?");
                $stmt->execute([$name, $description, $price, $image, $image, $id]);
                $_SESSION['message'] = "Menu item updated successfully.";
                $_SESSION['message_type'] = 'success';
            }
            header("Location: manage_menu.php");
            exit;
        }
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item && $item['image']) {
            unlink(UPLOAD_PATH . $item['image']);
        }
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Menu item deleted successfully.";
        $_SESSION['message_type'] = 'success';
        header("Location: manage_menu.php");
        exit;
    }
}

// Fetch menu items
$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY created_at DESC");
$menu_items = $stmt->fetchAll();

// Fetch item for editing
$edit_item = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

// Fetch admin name (assuming 'name' field in admins table; fallback to username)
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
    <title>Manage Menu - Bean & Brew</title>
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
                    <li><a href="manage_menu.php" class="active">Manage Menu</a></li>
                    <li><a href="manage_gallery.php">Manage Gallery</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h1>Manage Menu</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                </div>
            </header>
            <?php display_message(); ?>
            <section class="form-section">
                <h2><?php echo $edit_item ? 'Edit Menu Item' : 'Add Menu Item'; ?></h2>
                <form method="POST" enctype="multipart/form-data" id="menuForm" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'update' : 'add'; ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_item['name'] ?? ''); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <span class="error"><?php echo $errors['name']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="error"><?php echo $errors['description']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (Rs.)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($edit_item['price'] ?? ''); ?>" required>
                        <?php if (isset($errors['price'])): ?>
                            <span class="error"><?php echo $errors['price']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <div id="image-preview" class="image-preview">
                            <?php if ($edit_item && $edit_item['image']): ?>
                                <img src="../<?php echo UPLOAD_DIR . $edit_item['image']; ?>" alt="Current Image">
                            <?php endif; ?>
                        </div>
                        <?php if (isset($errors['image'])): ?>
                            <span class="error"><?php echo $errors['image']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="<?php echo $edit_item ? 'Update Item' : 'Add Item'; ?>">
                        <button type="reset" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </section>
            <section class="table-section">
                <h2>Menu Items</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menu_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <?php echo $item['image'] ? '<img src="../' . UPLOAD_DIR . $item['image'] . '" alt="' . htmlspecialchars($item['name']) . '" width="50">' : 'No image'; ?>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $item['id']; ?>" class="action-btn edit-btn">Edit</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="submit" value="Delete" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this item?');">
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
        document.getElementById('menuForm').addEventListener('submit', function(e) {
            let valid = true;
            const name = document.getElementById('name').value.trim();
            const description = document.getElementById('description').value.trim();
            const price = parseFloat(document.getElementById('price').value);

            document.querySelectorAll('.error').forEach(el => el.remove());

            if (!name) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Name is required.';
                document.getElementById('name').after(error);
            }
            if (!description) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Description is required.';
                document.getElementById('description').after(error);
            }
            if (isNaN(price) || price <= 0) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Valid price is required.';
                document.getElementById('price').after(error);
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