<?php
require_once 'config.php';

// Validate and sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if admin is logged in
function is_admin_logged_in() {
    if (isset($_SESSION['admin_id']) && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: " . SITE_URL . "admin/login.php");
    exit;
}

// Validate file upload
function validate_image_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload failed with error code " . $file['error'];
    }
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Only JPEG, PNG, and GIF files are allowed.";
    }
    if ($file['size'] > $max_size) {
        $errors[] = "File size exceeds 2MB limit.";
    }
    return $errors;
}

// Display error/success messages
function display_message() {
    if (isset($_SESSION['message'])) {
        echo '<div class="message ' . ($_SESSION['message_type'] ?? 'success') . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}
?>