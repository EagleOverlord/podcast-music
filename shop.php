<?php
require_once 'includes/db.php';
$page_title = 'Shop';

// get all the categories so we can show them as checkboxes in the sidebar
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// build up the SQL query depending on which filters the user has applied
$where    = ['1=1'];
$params   = [];
$types    = '';

// if the user ticked any category checkboxes, filter by those categories
$cat_filter = [];
if (!empty($_GET['cat']) && is_array($_GET['cat'])) {
    $cat_filter = array_map('intval', $_GET['cat']);
    if ($cat_filter) {
        $placeholders = implode(',', array_fill(0, count($cat_filter), '?'));
        $where[]      = "p.category_id IN ($placeholders)";
        $params       = array_merge($params, $cat_filter);
        $types       .= str_repeat('i', count($cat_filter));
    }
}

// if the user entered a min or max price, add those to the filter as well
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : null;

if ($price_min !== null) {
    $where[]  = 'p.price >= ?';
    $params[] = $price_min;
    $types   .= 'd';
}
if ($price_max !== null && $price_max > 0) {
    $where[]  = 'p.price <= ?';
    $params[] = $price_max;
    $types   .= 'd';
}

$where_sql = implode(' AND ', $where);
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_sql
        ORDER BY p.name ASC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// work out what heading to show above the products
if ($cat_filter && count($cat_filter) === 1) {
    $cat_names = array_column($categories, 'name', 'id');
    $heading   = $cat_names[$cat_filter[0]] ?? 'All Categories';
} else {
    $heading = 'All Categories';
}

require_once 'includes/header.php';
?>
<main>
<div class="container">
    <div class="shop-layout">

        <!-- Sidebar -->
        <aside class="shop-sidebar">
            <form method="GET" action="shop.php" id="filter-form">

                <!-- Categories -->
                <div class="sidebar-section">
                    <div class="sidebar-title">Category</div>
                    <?php foreach ($categories as $cat): ?>
                    <label class="sidebar-check">
                        <input type="checkbox" name="cat[]" value="<?= $cat['id'] ?>"
                               <?= in_array($cat['id'], $cat_filter) ? 'checked' : '' ?>
                               onchange="document.getElementById('filter-form').submit()">
                        <?= htmlspecialchars($cat['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Price -->
                <div class="sidebar-section">
                    <div class="sidebar-title">Price</div>
                    <div class="price-inputs">
                        <span>£</span>
                        <input type="number" name="price_min" min="0" step="0.01"
                               value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>"
                               placeholder="Min">
                        <span>–</span>
                        <input type="number" name="price_max" min="0" step="0.01"
                               value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>"
                               placeholder="Max">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-full">Apply</button>
                </div>

                <?php if ($cat_filter || $price_min !== null || $price_max): ?>
                <div class="sidebar-section">
                    <a href="shop.php" class="btn btn-outline btn-sm btn-full">
                        <span class="material-icons">close</span> Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </aside>

        <!-- Products -->
        <div>
            <div class="shop-header">
                <h1 class="section-title" style="margin-bottom:0;"><?= htmlspecialchars($heading) ?></h1>
                <span class="shop-count"><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?></span>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <span class="material-icons">search</span>
                <h3>No products found</h3>
                <p>Try adjusting your filters or <a href="shop.php" class="form-link">browse all products</a>.</p>
            </div>
            <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="product-card">
                    <img src="<?= htmlspecialchars($p['image']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         class="product-card-img">
                    <div class="product-card-body">
                        <div class="product-card-name">
                            <?= htmlspecialchars($p['name']) ?>
                            <?php if ($p['stock_quantity'] < 10): ?>
                                <span class="low-stock-pill">Low Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-price"><?= formatPrice($p['price']) ?> each</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
