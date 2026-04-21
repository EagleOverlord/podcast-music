<?php
require_once 'includes/db.php';
$page_title = 'Track Order';

$order_id = (int)($_GET['order'] ?? 0);
$order    = null;
$items    = [];
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
}

if ($order_id) {
    $stmt = $conn->prepare("SELECT o.*, u.first_name, u.last_name, u.email
                            FROM orders o
                            JOIN users u ON o.customer_id = u.id
                            WHERE o.id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $i_stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi
                                  JOIN products p ON oi.product_id = p.id
                                  WHERE oi.order_id = ?");
        $i_stmt->bind_param('i', $order_id);
        $i_stmt->execute();
        $items = $i_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = 'Order not found. Please check your order number.';
        $order_id = 0;
    }
}

// work out which step of the progress bar to highlight based on the order status
$step_map = ['ordered' => 0, 'processing' => 1, 'delivered' => 2];
$current_step = $order ? ($step_map[$order['status']] ?? 0) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_delivery']) && $order) {
    $new_type = in_array($_POST['new_delivery'], ['pickup','delivery']) ? $_POST['new_delivery'] : 'pickup';
    if (in_array($order['status'], ['ordered','processing'])) {
        $upd = $conn->prepare("UPDATE orders SET delivery_type = ? WHERE id = ?");
        $upd->bind_param('si', $new_type, $order_id);
        $upd->execute();
        flash('Delivery type updated.', 'success');
        redirect(BASE_URL . 'track-order.php?order=' . $order_id);
    }
}

require_once 'includes/header.php';
?>
<main>
<div class="container">
<div class="track-wrapper">

    <h1 style="font-size:1.7rem;font-weight:900;margin-bottom:20px;">Track Your Order</h1>

    <!-- form where the user types in their order number -->
    <div class="track-card" style="margin-bottom:18px;">
        <form method="POST" action="track-order.php" style="display:flex;gap:12px;align-items:flex-end;">
            <div style="flex:1;">
                <label class="form-label" for="order_id">Order Number:</label>
                <input class="form-input" type="number" id="order_id" name="order_id"
                       placeholder="e.g. 1" min="1"
                       value="<?= $order_id ?: '' ?>" style="max-width:200px;">
            </div>
            <button type="submit" class="btn btn-primary">
                <span class="material-icons">search</span> Track
            </button>
        </form>
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-top:14px;margin-bottom:0;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
        <div style="margin-top:14px;font-size:.85rem;color:var(--text-mid);">
            Or <a href="account.php" class="form-link">view all your orders</a> from your account.
        </div>
        <?php endif; ?>
    </div>

    <?php if ($order): ?>

    <!-- card showing the order reference and progress bar -->
    <div class="track-card">
        <div class="track-order-ref">Order #<?= str_pad($order['id'], 3, '0', STR_PAD_LEFT) ?></div>
        <h2 class="track-heading">Track Your Order</h2>

        <div class="progress-wrap">
            <?php
            $steps = ['Ordered', 'Processing', 'Delivered'];
            foreach ($steps as $i => $label):
                $cls = $i < $current_step ? 'done' : ($i === $current_step ? 'active' : '');
            ?>
            <div class="step">
                <div class="step-circle <?= $cls ?>"><?= $i + 1 ?></div>
                <span class="step-name <?= $cls ?>"><?= $label ?></span>
            </div>
            <?php if ($i < count($steps) - 1): ?>
            <div class="step-line <?= $i < $current_step ? 'done' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- card showing the full details of the order -->
    <div class="track-card">
        <h3 style="font-weight:900;font-size:1.1rem;margin-bottom:18px;">Order Details</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
            <div>
                <div class="order-detail-label">Ordered on</div>
                <div class="order-detail-value">
                    <?= date('F jS, Y', strtotime($order['created_at'])) ?>
                </div>
            </div>
            <div>
                <div class="order-detail-label">Status</div>
                <div><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></div>
            </div>
            <div>
                <div class="order-detail-label">Delivery Type</div>
                <div class="order-detail-value" style="text-transform:capitalize;">
                    <?= htmlspecialchars($order['delivery_type']) ?>
                </div>
            </div>
            <?php if ($order['delivery_address']): ?>
            <div>
                <div class="order-detail-label">Address</div>
                <div class="order-detail-value" style="font-size:.88rem;"><?= htmlspecialchars($order['delivery_address']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- table listing all the items in the order -->
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th style="text-align:right;">Price</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td style="text-align:right;"><?= formatPrice($item['price']) ?></td>
                    <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="basket-total-row" style="margin-top:12px;">
            <span>Total Price:</span>
            <span class="basket-total-price"><?= formatPrice($order['total_price']) ?></span>
        </div>
    </div>

    <!-- lets the user change the delivery type if the order hasnt been delivered yet -->
    <?php if ($order['status'] !== 'delivered'): ?>
    <div class="track-card">
        <h3 style="font-weight:900;font-size:1rem;margin-bottom:14px;">Edit Order</h3>
        <form method="POST" action="track-order.php?order=<?= $order_id ?>"
              style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <input type="hidden" name="update_delivery" value="1">
            <div>
                <label class="form-label" style="margin-bottom:4px;">Change delivery type:</label>
                <select name="new_delivery" class="form-input" style="width:auto;padding:8px 14px;">
                    <option value="pickup"   <?= $order['delivery_type']==='pickup'   ? 'selected' : '' ?>>Pickup</option>
                    <option value="delivery" <?= $order['delivery_type']==='delivery' ? 'selected' : '' ?>>Delivery</option>
                </select>
            </div>
            <button type="submit" class="btn btn-dark" style="margin-top:18px;">
                <span class="material-icons">save</span> Update Order
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php endif; ?>

</div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
