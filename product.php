<?php
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect(BASE_URL . 'shop.php');
}

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    flash('Product not found.', 'error');
    redirect(BASE_URL . 'shop.php');
}

$page_title = $product['name'];

// Related products (same category, exclude current)
$rel_stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 3");
$rel_stmt->bind_param('ii', $product['category_id'], $id);
$rel_stmt->execute();
$related = $rel_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>
<main>
<div class="container">
    <div class="product-detail-layout">

        <!-- Sidebar -->
        <aside class="product-detail-sidebar">
            <img src="<?= htmlspecialchars($product['image']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="product-thumb">
            <div class="product-sidebar-name"><?= htmlspecialchars($product['name']) ?></div>
            <div style="margin-top:6px;">
                <span class="product-sidebar-price"><?= formatPrice($product['price']) ?> each</span>
            </div>

            <?php if ($related): ?>
            <hr style="margin:16px 0;border:none;border-top:1px solid var(--gray-light);">
            <div class="sidebar-title" style="font-size:.8rem;margin-bottom:10px;">Related Products</div>
            <?php foreach ($related as $r): ?>
            <a href="product.php?id=<?= $r['id'] ?>" style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--gray-xlight);text-decoration:none;">
                <img src="<?= htmlspecialchars($r['image']) ?>" alt=""
                     style="width:44px;height:38px;object-fit:cover;border-radius:5px;">
                <div>
                    <div style="font-weight:700;font-size:.82rem;color:var(--text);"><?= htmlspecialchars($r['name']) ?></div>
                    <div style="font-size:.78rem;color:var(--green-primary);font-weight:700;"><?= formatPrice($r['price']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <!-- Main Content -->
        <div class="product-detail-main">
            <?php if ($product['category_name']): ?>
                <div class="product-label"><?= htmlspecialchars($product['category_name']) ?></div>
            <?php endif; ?>

            <h1 class="product-detail-name">Product Name: <?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-detail-price"><?= formatPrice($product['price']) ?></div>

            <p class="product-detail-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php if ($product['stock_quantity'] > 0): ?>
            <form action="basket-action.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <div class="qty-row">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity"
                           class="qty-input" value="1" min="1"
                           max="<?= min($product['stock_quantity'], 99) ?>">
                </div>

                <div class="stock-info <?= $product['stock_quantity'] < 10 ? 'low' : '' ?>">
                    <?php if ($product['stock_quantity'] < 10): ?>
                        <span class="material-icons" style="font-size:16px;vertical-align:middle;">warning</span> Only <?= $product['stock_quantity'] ?> left in stock
                    <?php else: ?>
                        <span class="material-icons" style="font-size:16px;vertical-align:middle;">check_circle</span> In stock (<?= $product['stock_quantity'] ?> available)
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="margin-top:20px;">
                    <span class="material-icons">shopping_basket</span> Add to Basket
                </button>
            </form>
            <?php else: ?>
            <div class="alert alert-error" style="margin-top:16px;">
                <span class="material-icons" style="font-size:16px;vertical-align:middle;">cancel</span> Out of stock — check back soon.
            </div>
            <?php endif; ?>

            <div style="margin-top:24px;">
                <a href="shop.php" class="btn btn-outline btn-sm">
                    <span class="material-icons">arrow_back</span> Back to Shop
                </a>
            </div>
        </div>

    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
