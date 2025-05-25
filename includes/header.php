<?php
// Define the current page to highlight active navigation link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <img src="https://encrypted-tbn0.gstatic.com/images?q=3tbn:ANd9GcTU1qyLeec-kd3HMzFObK4WXg9WgVaUlcyQXw&s"
         alt="Bean & Brew" class="logo" width="60" height="60">
    <nav>
        <ul>
            <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'style="background: #d2691e;"' : ''; ?>>Home</a></li>
            <li><a href="about.php#our-story" <?php echo $current_page == 'about.php' ? 'style="background: #d2691e;"' : ''; ?>>About</a></li>
            <li><a href="menu.php" <?php echo $current_page == 'menu.php' ? 'style="background: #d2691e;"' : ''; ?>>Menu</a></li>
            <li><a href="contact.php#location" <?php echo $current_page == 'contact.php' ? 'style="background: #d2691e;"' : ''; ?>>Contact</a></li>
        </ul>
    </nav>
</header>