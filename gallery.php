<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bean & Brew - Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/hero.php'; ?>
    <?php include 'includes/header.php'; ?>
<main>    <div class="gallery">
        <h2>Our Gallery</h2>
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo UPLOAD_DIR . htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['caption'] ?? 'Gallery Image'); ?>">
                    <p><?php echo htmlspecialchars($image['caption'] ?? ''); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div></main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>