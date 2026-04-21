<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="producer-sidebar">
    <div class="producer-sidebar-header">
        <div class="producer-sidebar-title">Producer Hub</div>
        <div class="producer-sidebar-sub"><?= htmlspecialchars($_SESSION['user_first_name'] ?? 'Producer') ?></div>
    </div>
    <nav>
        <a href="<?= BASE_URL ?>producer/dashboard.php"
           class="producer-nav-link <?= $current==='dashboard.php' ? 'active':'' ?>">
            <span class="material-icons">bar_chart</span> Dashboard
        </a>
        <a href="<?= BASE_URL ?>producer/products.php"
           class="producer-nav-link <?= $current==='products.php' ? 'active':'' ?>">
            <span class="material-icons">inventory_2</span> My Products
        </a>
        <a href="<?= BASE_URL ?>producer/orders.php"
           class="producer-nav-link <?= $current==='orders.php' ? 'active':'' ?>">
            <span class="material-icons">assignment</span> Orders
        </a>
        <a href="<?= BASE_URL ?>producer/add-product.php"
           class="producer-nav-link <?= $current==='add-product.php' ? 'active':'' ?>">
            <span class="material-icons">add</span> Add Product
        </a>
        <a href="<?= BASE_URL ?>shop.php" class="producer-nav-link">
            <span class="material-icons">storefront</span> View Shop
        </a>
        <a href="<?= BASE_URL ?>logout.php"
           class="producer-nav-link"
           style="border-top:1px solid rgba(255,255,255,.1);margin-top:8px;">
            <span class="material-icons">logout</span> Log Out
        </a>
    </nav>
</aside>
