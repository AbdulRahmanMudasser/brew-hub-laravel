<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll();
?>
<main>
    <div class="gallery">
        <h2>Our Gallery</h2>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
            <div class="gallery-item">
                <img src="<?php echo UPLOAD_DIR . htmlspecialchars($image['filename']); ?>"
                    alt="<?php echo htmlspecialchars($image['caption'] ?? 'Gallery Image'); ?>">
                <p style="margin-top: 10px;">
                    <?php echo htmlspecialchars($image['caption'] ?? ''); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>