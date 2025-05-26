<?php
require_once 'config.php';
require_once 'functions.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Handle visitor preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_preferences'])) {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $favorite_color = sanitize_input($_POST['favorite_color'] ?? '');
    if (!empty($full_name) && !empty($favorite_color)) {
        // Validate favorite_color as a CSS color (basic check)
        if (preg_match('/^#[0-9A-Fa-f]{6}$|^[a-zA-Z]+$/', $favorite_color)) {
            setcookie('user_full_name', $full_name, time() + (30 * 24 * 60 * 60), '/');
            setcookie('favorite_color', $favorite_color, time() + (30 * 24 * 60 * 60), '/');
            $_SESSION['message'] = "Preferences saved!";
            $_SESSION['message_type'] = 'success';
            $_SESSION['show_popup'] = true; // Trigger popup
        } else {
            $_SESSION['message'] = "Invalid color format. Use hex (#RRGGBB) or color name.";
            $_SESSION['message_type'] = 'error';
        }
        header("Location: $current_page");
        exit;
    } else {
        $_SESSION['message'] = "Both fields are required.";
        $_SESSION['message_type'] = 'error';
    }
}

$full_name = $_COOKIE['user_full_name'] ?? '';
$favorite_color = $_COOKIE['favorite_color'] ?? '';
?>
<header>
    <img src="https://images.unsplash.com/photo-1511920170033-f8396924c312?q=80&w=60&auto=format&fit=crop" alt="Bean & Brew Logo" class="logo" width="60" height="60">
    <button class="hamburger" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav class="nav-menu">
        <ul>
            <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
            <li><a href="menu.php" <?php echo $current_page == 'menu.php' ? 'class="active"' : ''; ?>>Menu</a></li>
            <li><a href="gallery.php" <?php echo $current_page == 'gallery.php' ? 'class="active"' : ''; ?>>Gallery</a></li>
            <li><a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
        </ul>
    </nav>
</header>
<div class="header-extras">
    <!-- <?php if ($full_name): ?>
        <div class="welcome" style="color: <?php echo htmlspecialchars($favorite_color); ?>;">
            Welcome, <?php echo htmlspecialchars($full_name); ?>!
        </div>
    <?php endif; ?> -->
    <form method="POST" class="preferences-form">
        <input type="hidden" name="set_preferences" value="1">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your name" required>
        <label for="favorite_color">Favorite Color</label>
        <input type="text" id="favorite_color" name="favorite_color" value="<?php echo htmlspecialchars($favorite_color); ?>" placeholder="e.g., #6f4e37 or brown" required>
        <input type="submit" value="Save Preferences">
    </form>
    <?php display_message(); ?>
</div>
<?php if (isset($_SESSION['show_popup']) && $full_name && $favorite_color): ?>
    <div class="preference-popup">
        <button class="popup-close" aria-label="Close popup">✕</button>
        <p style="color: <?php echo htmlspecialchars($favorite_color); ?>;">
            Hello, <?php echo htmlspecialchars($full_name); ?>!
        </p>
    </div>
    <?php unset($_SESSION['show_popup']); // Clear popup trigger ?>
<?php endif; ?>