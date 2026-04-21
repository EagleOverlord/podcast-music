<?php
require_once 'includes/db.php';
$page_title = 'Home';

$featured = $conn->query("SELECT * FROM products WHERE featured = 1 LIMIT 2")->fetch_all(MYSQLI_ASSOC);

$recent = $conn->query("SELECT p.*, c.name AS category_name
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        ORDER BY p.id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>
<main>

    <section class="hero">
        <div class="container hero-content">
            <h1>Eat Fresh and<br>Buy Local</h1>
            <p>Supporting local farmers &amp; bringing the freshest produce straight to your door.</p>
            <a href="shop.php" class="btn btn-primary btn-lg">
                <span class="material-icons">storefront</span> Shop Now
            </a>
        </div>
    </section>

    <?php if (count($featured) >= 2): ?>
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title text-center">Featured Products</h2>
            <div class="section-divider" style="margin:0 auto 30px;"></div>
            <div class="featured-inner">
                <a href="product.php?id=<?= $featured[0]['id'] ?>" class="featured-card">
                    <img src="<?= htmlspecialchars($featured[0]['image']) ?>"
                         alt="<?= htmlspecialchars($featured[0]['name']) ?>"
                         class="featured-card-img">
                    <div class="featured-card-label"><?= htmlspecialchars($featured[0]['name']) ?></div>
                </a>

                <span class="featured-and">&amp;</span>

                <a href="product.php?id=<?= $featured[1]['id'] ?>" class="featured-card">
                    <img src="<?= htmlspecialchars($featured[1]['image']) ?>"
                         alt="<?= htmlspecialchars($featured[1]['name']) ?>"
                         class="featured-card-img">
                    <div class="featured-card-label"><?= htmlspecialchars($featured[1]['name']) ?></div>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section style="padding: 50px 0; background: var(--bg);">
        <div class="container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px;">
                <div>
                    <h2 class="section-title" style="margin-bottom:4px;">Fresh Arrivals</h2>
                    <div class="section-divider"></div>
                </div>
                <a href="shop.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="products-grid">
                <?php foreach ($recent as $p): ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="product-card">
                    <img src="<?= htmlspecialchars($p['image']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         class="product-card-img">
                    <div class="product-card-body">
                        <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-card-price"><?= formatPrice($p['price']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


</main>
<?php require_once 'includes/footer.php'; ?>
