<?php
// Database configuration for InfinityFree
define('DB_HOST', 'localhost'); // Replace with InfinityFree DB host
define('DB_USER', 'root');     // Replace with your DB username
define('DB_PASS', '');         // Replace with your DB password
define('DB_NAME', 'bean_brew'); // Replace with your DB name

// Site configuration
define('SITE_URL', 'http://your-username.infinityfreeapp.com/'); // Replace with your domain
define('UPLOAD_DIR', 'uploads/gallery/');
define('UPLOAD_PATH', __DIR__ . '/../' . UPLOAD_DIR);
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
session_start();

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>