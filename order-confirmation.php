<?php
require_once 'includes/db.php';
$page_title = 'Order Confirmed';

$order_id = (int)($_GET['order'] ?? $_SESSION['last_order_id'] ?? 0);

$order = null;
if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
}

require_once 'includes/header.php';
?>
<main>
<div class="container">
<div class="confirm-wrapper">

    <div class="confirm-icon">
        <span class="material-icons">check</span>
    </div>

    <h1 style="font-size:2rem;font-weight:900;margin-bottom:10px;">Order Confirmed!</h1>
    <p style="color:var(--text-mid);font-size:1rem;margin-bottom:28px;">
        Thank you for shopping with Greenfield Local Hub. Your order has been received and
        is being prepared.
    </p>

    <?php if ($order): ?>
    <div class="checkout-card" style="text-align:left;margin-bottom:20px;">
        <h2 style="font-weight:800;margin-bottom:14px;font-size:1rem;">Order Details</h2>

        <div class="summary-row">
            <span>Order Reference</span>
            <span style="font-weight:700;color:var(--green-primary);">#<?= str_pad($order['id'], 3, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="summary-row">
            <span>Status</span>
            <span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
        </div>
        <div class="summary-row">
            <span>Delivery Type</span>
            <span style="text-transform:capitalize;"><?= htmlspecialchars($order['delivery_type']) ?></span>
        </div>
        <?php if ($order['delivery_address']): ?>
        <div class="summary-row">
            <span>Address</span>
            <span><?= htmlspecialchars($order['delivery_address']) ?></span>
        </div>
        <?php endif; ?>
        <div class="summary-row total">
            <span>Total Paid</span>
            <span><?= formatPrice($order['total_price']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <?php if ($order_id): ?>
        <a href="track-order.php?order=<?= $order_id ?>" class="btn btn-primary">
            <span class="material-icons">location_on</span> Track My Order
        </a>
        <?php endif; ?>
        <a href="shop.php" class="btn btn-secondary">
            <span class="material-icons">storefront</span> Continue Shopping
        </a>
    </div>

</div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
