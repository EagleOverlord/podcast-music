<?php
$basket_count = getBasketCount();
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — Greenfield Local Hub' : 'Greenfield Local Hub' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <a href="<?= BASE_URL ?>" class="nav-brand">
            <img src="<?= BASE_URL ?>assets/logo.png" alt="Logo" class="nav-logo">
            <span class="nav-brand-name">Greenfield Local Hub</span>
        </a>

        <div class="nav-links">
            <a href="<?= BASE_URL ?>index.php"
               class="nav-btn <?= $current_page === 'index.php' ? 'active' : '' ?>">Home Page</a>
            <a href="<?= BASE_URL ?>shop.php"
               class="nav-btn <?= $current_page === 'shop.php' ? 'active' : '' ?>">Products</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isProducer()): ?>
                    <a href="<?= BASE_URL ?>producer/dashboard.php"
                       class="nav-btn <?= $current_dir === 'producer' ? 'active' : '' ?>">My Account</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>account.php"
                       class="nav-btn <?= $current_page === 'account.php' ? 'active' : '' ?>">My Account</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php"
                   class="nav-btn <?= $current_page === 'login.php' ? 'active' : '' ?>">My Account</a>
            <?php endif; ?>
        </div>

        <a href="<?= BASE_URL ?>basket.php" class="nav-cart" title="View basket">
            <span class="material-icons">shopping_basket</span>
            <?php if ($basket_count > 0): ?>
                <span class="cart-badge"><?= $basket_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</nav>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="flash-message flash-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?>">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>
