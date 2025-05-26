<?php
require_once 'config.php';
require_once 'functions.php';

$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY name");
$menu_items = $stmt->fetchAll();
?>
<section class="menu">
    <h2>Our Menu</h2>
    <div class="menu-grid">
        <?php foreach ($menu_items as $item): ?>
            <div class="menu-item" data-item-id="<?php echo htmlspecialchars($item['id']); ?>">
                <?php if ($item['image']): ?>
                    <img src="<?php echo UPLOAD_DIR . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-image">
                <?php else: ?>
                    <div class="menu-image-placeholder">No Image Available</div>
                <?php endif; ?>
                <div class="menu-content">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="price">Rs. <?php echo number_format($item['price']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>