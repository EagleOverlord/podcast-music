<?php
require_once 'includes/db.php';
$page_title    = 'Your Basket';
$basket        = $_SESSION['basket']        ?? [];
$delivery_type = $_SESSION['delivery_type'] ?? 'pickup';
$total         = getBasketTotal();

require_once 'includes/header.php';
?>
<main>
<div class="container">
    <div style="padding:24px 0 10px;">
        <h1 style="font-size:1.7rem;font-weight:900;">Your Basket</h1>
    </div>

    <?php if (empty($basket)): ?>
    <div class="empty-state">
        <span class="material-icons">shopping_basket</span>
        <h3>Your basket is empty</h3>
        <p>Browse our fresh local produce and add something delicious.</p>
        <a href="shop.php" class="btn btn-primary"><span class="material-icons">storefront</span> Browse Products</a>
    </div>
    <?php else: ?>

    <div class="basket-layout">

        <div>
            <div class="basket-card">
                <h2 class="basket-heading">Your Basket</h2>

                <?php foreach ($basket as $pid => $item): ?>
                <div class="basket-item">
                    <img src="<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['name']) ?>"
                         class="basket-item-img">

                    <div>
                        <div class="basket-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="basket-item-price"><?= formatPrice($item['price']) ?> each</div>

                        <div class="qty-controls" style="margin-top:10px;">
                            <a href="basket-action.php?action=decrease&product_id=<?= $pid ?>"
                               class="qty-btn" title="Decrease">&#8722;</a>
                            <span class="qty-display">Current: <?= $item['quantity'] ?></span>
                            <a href="basket-action.php?action=increase&product_id=<?= $pid ?>"
                               class="qty-btn" title="Increase">&#43;</a>
                        </div>
                    </div>

                    <div class="basket-item-right">
                        <span class="item-total">Total &#8212; <?= formatPrice($item['price'] * $item['quantity']) ?></span>
                        <form action="basket-action.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $pid ?>">
                            <button class="btn-remove" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <div class="basket-card" style="margin-bottom:16px;">
                <h3 style="font-weight:800;margin-bottom:16px;font-size:1rem;">Delivery Option</h3>
                <form action="basket-action.php" method="POST" id="delivery-form">
                    <input type="hidden" name="action" value="delivery">

                    <label class="delivery-option-label" style="<?= $delivery_type === 'pickup' ? 'border-color:var(--green-primary);background:var(--green-light);' : '' ?>">
                        <input type="radio" name="delivery_type" value="pickup"
                               <?= $delivery_type === 'pickup' ? 'checked' : '' ?>
                               onchange="this.form.submit()">
                        <span class="delivery-option-icon"><span class="material-icons">storefront</span></span>
                        <span>Pickup</span>
                    </label>

                    <label class="delivery-option-label" style="<?= $delivery_type === 'delivery' ? 'border-color:var(--green-primary);background:var(--green-light);' : '' ?>">
                        <input type="radio" name="delivery_type" value="delivery"
                               <?= $delivery_type === 'delivery' ? 'checked' : '' ?>
                               onchange="this.form.submit()">
                        <span class="delivery-option-icon"><span class="material-icons">local_shipping</span></span>
                        <span>Delivery</span>
                    </label>
                </form>
            </div>

            <div class="basket-card">
                <h3 style="font-weight:800;margin-bottom:16px;font-size:1rem;">Order Summary</h3>

                <?php foreach ($basket as $item): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:.88rem;border-bottom:1px solid var(--gray-xlight);">
                    <span><?= htmlspecialchars($item['name']) ?> &times;<?= $item['quantity'] ?></span>
                    <span style="font-weight:700;"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                </div>
                <?php endforeach; ?>

                <div class="basket-total-row">
                    <span>Total Price:</span>
                    <span class="basket-total-price"><?= formatPrice($total) ?></span>
                </div>

                <a href="checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:16px;">
                    <span class="material-icons">lock</span> Proceed to Checkout
                </a>
            </div>
        </div>

    </div>
    <?php endif; ?>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
