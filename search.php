<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$search_query = sanitize_input($_GET['q'] ?? '');
$results = [];

if (!empty($search_query)) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE name LIKE ? OR description LIKE ?");
    $stmt->execute(["%$search_query%", "%$search_query%"]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bean & Brew - Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/hero.php'; ?>
    <?php include 'includes/header.php'; ?>
    <main><div class="search">
        <h2>Search Menu</h2>
        <form method="GET" class="search-form">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search menu items...">
            <input type="submit" value="Search">
        </form>
        <?php if (!empty($search_query)): ?>
            <h3>Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
            <div class="menu-grid">
                <?php if (empty($results)): ?>
                    <p>No results found.</p>
                <?php else: ?>
                    <?php foreach ($results as $item): ?>
                        <div class="menu-item">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo UPLOAD_DIR . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="250">
                            <?php endif; ?>
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div></main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>