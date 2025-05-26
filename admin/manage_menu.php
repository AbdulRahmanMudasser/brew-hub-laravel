<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

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
        if (empty($name) || empty($description) || $price === false || $price <= 0) {
            $_SESSION['message'] = "All fields are required and price must be valid.";
            $_SESSION['message_type'] = 'error';
        } else {
            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $errors = validate_image_upload($_FILES['image']);
                if (empty($errors)) {
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $image);
                } else {
                    $_SESSION['message'] = implode(" ", $errors);
                    $_SESSION['message_type'] = 'error';
                    header("Location: manage_menu.php");
                    exit;
                }
            }

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
        }
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Menu item deleted successfully.";
        $_SESSION['message_type'] = 'success';
    }
    header("Location: manage_menu.php");
    exit;
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Menu - Bean & Brew</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Manage Menu</h1>
        <?php display_message(); ?>
        <h2><?php echo $edit_item ? 'Edit Menu Item' : 'Add Menu Item'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="<?php echo $edit_item ? 'update' : 'add'; ?>">
            <?php if ($edit_item): ?>
                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
            <?php endif; ?>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo $edit_item['name'] ?? ''; ?>" required>
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?php echo $edit_item['description'] ?? ''; ?></textarea>
            <label for="price">Price (Rs.)</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $edit_item['price'] ?? ''; ?>" required>
            <label for="image">Image</label>
            <input type="file" id="image" name="image" accept="image/*">
            <input type="submit" value="<?php echo $edit_item ? 'Update Item' : 'Add Item'; ?>">
        </form>
        <h2>Menu Items</h2>
        <table>
            <tr><th>Name</th><th>Price</th><th>Image</th><th>Actions</th></tr>
            <?php foreach ($menu_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['image'] ? '<img src="../' . UPLOAD_DIR . $item['image'] . '" width="50">' : 'No image'; ?></td>
                    <td>
                        <a href="?edit=<?php echo $item['id']; ?>">Edit</a> |
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <input type="submit" value="Delete" onclick="return confirm('Are you sure?');">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="logout">
            <a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>