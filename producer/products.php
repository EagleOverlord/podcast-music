<?php
require_once '../includes/db.php';
requireProducer();

$page_title  = 'Manage Products';
$producer_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id  = (int)$_GET['delete'];
    $del_chk = $conn->prepare("SELECT id FROM products WHERE id = ? AND producer_id = ?");
    $del_chk->bind_param('ii', $del_id, $producer_id);
    $del_chk->execute();
    if ($del_chk->get_result()->num_rows) {
        $conn->prepare("DELETE FROM products WHERE id = ?")->bind_param('i', $del_id) && true;
        $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $del_stmt->bind_param('i', $del_id);
        $del_stmt->execute();
        flash('Product deleted.', 'info');
    }
    redirect(BASE_URL . 'producer/products.php');
}

// Fetch products
$stmt = $conn->prepare("SELECT p.*, c.name AS cat FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.producer_id = ?
                        ORDER BY p.name ASC");
$stmt->bind_param('i', $producer_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>
<main>
<div class="container">
<div class="producer-layout">

    <?php include 'producer-sidebar.php'; ?>

    <div>
        <h1 class="manage-heading">Edit and update products</h1>

        <?php if (empty($products)): ?>
        <div class="empty-state">
            <span class="material-icons">📦</span>
            <h3>No products yet</h3>
            <p>Add your first product to get started.</p>
        </div>
        <?php else: ?>

        <?php foreach ($products as $p): ?>
        <div class="product-row">
            <img src="<?= htmlspecialchars('../' . $p['image']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 class="product-row-img">

            <div>
                <div class="product-row-name">
                    Name: <?= htmlspecialchars($p['name']) ?>
                    <?php if ($p['stock_quantity'] < 10): ?>
                        <span class="low-stock-pill">Low Stock</span>
                    <?php endif; ?>
                </div>
                <div class="product-row-desc">
                    Description: <?= htmlspecialchars(substr($p['description'] ?? '', 0, 100)) ?>...
                </div>
                <div class="product-row-stock <?= $p['stock_quantity'] < 10 ? 'low' : '' ?>">
                    Stock: <?= $p['stock_quantity'] ?> | Category: <?= htmlspecialchars($p['cat'] ?? 'N/A') ?>
                </div>
            </div>

            <div class="product-row-price">
                Price: <?= formatPrice($p['price']) ?>
            </div>

            <div class="product-row-actions" style="grid-column:4;">
                <a href="add-product.php?edit=<?= $p['id'] ?>" class="icon-btn icon-btn-edit">
                    <span class="material-icons">✏️</span> Edit
                </a>
                <a href="products.php?delete=<?= $p['id'] ?>"
                   class="icon-btn icon-btn-delete"
                   onclick="return confirm('Delete <?= htmlspecialchars($p['name'], ENT_QUOTES) ?>?')">
                    <span class="material-icons">🗑️</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <a href="add-product.php" class="add-product-btn">
            <span class="material-icons">➕</span> Add New Product/s
        </a>
    </div>

</div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>
