<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['message'] = "Invalid CSRF token.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit;
    }

    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username)) $errors['username'] = "Username is required.";
    if (empty($password)) $errors['password'] = "Password is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['last_activity'] = time();
            header("Location: dashboard.php");
            exit;
        } else {
            $errors['login'] = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bean & Brew</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Arial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <h1>Admin Login</h1>
            <?php display_message(); ?>
            <?php if (isset($errors['login'])): ?>
                <div class="message error"><?php echo $errors['login']; ?></div>
            <?php endif; ?>
            <form method="POST" id="loginForm" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <?php if (isset($errors['username'])): ?>
                        <span class="error"><?php echo $errors['username']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-actions">
                    <input type="submit" value="Login">
                </div>
            </form>
        </div>
    </div>
    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let valid = true;
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            document.querySelectorAll('.error').forEach(el => el.remove());

            if (!username) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Username is required.';
                document.getElementById('username').after(error);
            }
            if (!password) {
                valid = false;
                const error = document.createElement('span');
                error.className = 'error';
                error.textContent = 'Password is required.';
                document.getElementById('password').after(error);
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>