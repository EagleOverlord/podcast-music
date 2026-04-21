<?php
require_once '../includes/db.php';
requireProducer();

$page_title = 'Manage Orders';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id  = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'] ?? '';
    if (in_array($new_status, ['ordered','processing','delivered']) && $order_id) {
        $upd = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $upd->bind_param('si', $new_status, $order_id);
        $upd->execute();
        flash('Order status updated.', 'success');
    }
    redirect(BASE_URL . 'producer/orders.php');
}

// Filter by status
$status_filter = $_GET['status'] ?? '';
$where = '';
$params = [];
$types  = '';

if ($status_filter && in_array($status_filter, ['ordered','processing','delivered'])) {
    $where  = "WHERE o.status = ?";
    $params = [$status_filter];
    $types  = 's';
}

$sql = "SELECT o.*, u.first_name, u.last_name, u.email,
               COUNT(oi.id) AS item_count
        FROM orders o
        JOIN users u ON o.customer_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        $where
        GROUP BY o.id
        ORDER BY o.created_at DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

require_once '../includes/header.php';
?>
<main>
<div class="container">
<div class="producer-layout">

    <?php include 'producer-sidebar.php'; ?>

    <div>
        <h1 class="manage-heading">Edit and manage orders</h1>

        <!-- Status filter tabs -->
        <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
            <a href="orders.php" class="btn btn-sm <?= !$status_filter ? 'btn-dark':'btn-outline' ?>">All</a>
            <a href="orders.php?status=ordered"    class="btn btn-sm <?= $status_filter==='ordered'    ? 'btn-dark':'btn-outline' ?>">Ordered</a>
            <a href="orders.php?status=processing" class="btn btn-sm <?= $status_filter==='processing' ? 'btn-dark':'btn-outline' ?>">Processing</a>
            <a href="orders.php?status=delivered"  class="btn btn-sm <?= $status_filter==='delivered'  ? 'btn-dark':'btn-outline' ?>">Delivered</a>
        </div>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <span class="material-icons">assignment</span>
            <h3>No orders found</h3>
            <p>Orders will appear here once customers place them.</p>
        </div>
        <?php else: ?>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td style="font-weight:800;">#<?= str_pad($o['id'],3,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                    <td style="font-size:.82rem;color:var(--text-mid);"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><?= $o['item_count'] ?></td>
                    <td style="font-weight:700;color:var(--green-primary);"><?= formatPrice($o['total_price']) ?></td>
                    <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td style="text-transform:capitalize;"><?= htmlspecialchars($o['delivery_type']) ?></td>
                    <td>
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="new_status" class="form-input"
                                    style="padding:5px 10px;font-size:.8rem;width:auto;">
                                <option value="ordered"    <?= $o['status']==='ordered'    ? 'selected':'' ?>>Ordered</option>
                                <option value="processing" <?= $o['status']==='processing' ? 'selected':'' ?>>Processing</option>
                                <option value="delivered"  <?= $o['status']==='delivered'  ? 'selected':'' ?>>Delivered</option>
                            </select>
                            <button type="submit" class="icon-btn icon-btn-edit" title="Update">
                                <span class="material-icons">save</span>
                            </button>
                            <a href="<?= BASE_URL ?>track-order.php?order=<?= $o['id'] ?>"
                               class="icon-btn icon-btn-edit" title="View order" target="_blank">
                                <span class="material-icons">open_in_new</span>
                            </a>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>

</div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>
