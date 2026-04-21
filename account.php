<?php
require_once 'includes/db.php';
requireLogin();

$page_title = 'My Account';
$user_id    = $_SESSION['user_id'];

$u_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->bind_param('i', $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

$success = '';
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name']  ?? '');
    $addr  = trim($_POST['address']    ?? '');

    if ($first && $last) {
        $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, address=? WHERE id=?");
        $upd->bind_param('sssi', $first, $last, $addr, $user_id);
        $upd->execute();
        $_SESSION['user_name']       = $first . ' ' . $last;
        $_SESSION['user_first_name'] = $first;
        $success = 'Profile updated successfully.';
        $u_stmt->execute();
        $user = $u_stmt->get_result()->fetch_assoc();
    } else {
        $error = 'Name fields cannot be empty.';
    }
}

$o_stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$o_stmt->bind_param('i', $user_id);
$o_stmt->execute();
$orders = $o_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$tab = $_GET['tab'] ?? 'orders';

require_once 'includes/header.php';
?>
<main>
<div class="container">
<div class="account-layout">

    <!-- Sidebar -->
    <aside class="account-sidebar">
        <div class="account-sidebar-header">
            <div class="account-sidebar-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
            <div class="account-sidebar-role">Customer Account</div>
        </div>
        <nav>
            <a href="account.php?tab=orders" class="account-nav-link <?= $tab==='orders' ? 'active':'' ?>">
                <span class="material-icons">inventory_2</span> My Orders
            </a>
            <a href="account.php?tab=profile" class="account-nav-link <?= $tab==='profile' ? 'active':'' ?>">
                <span class="material-icons">person</span> My Details
            </a>
            <a href="shop.php" class="account-nav-link">
                <span class="material-icons">storefront</span> Browse Products
            </a>
            <a href="logout.php" class="account-nav-link" style="border-top:1px solid rgba(255,255,255,.1);margin-top:8px;">
                <span class="material-icons">logout</span> Log Out
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <div>

        <?php if ($tab === 'orders'): ?>
        <div class="account-card">
            <h2 class="account-card-title">My Orders</h2>
            <?php if (empty($orders)): ?>
            <div class="empty-state" style="padding:40px 0;">
                <span class="material-icons">inventory_2</span>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here.</p>
                <a href="shop.php" class="btn btn-primary">Browse Products</a>
            </div>
            <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td style="font-weight:700;">#<?= str_pad($o['id'],3,'0',STR_PAD_LEFT) ?></td>
                        <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                        <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="text-transform:capitalize;"><?= htmlspecialchars($o['delivery_type']) ?></td>
                        <td style="font-weight:700;color:var(--green-primary);"><?= formatPrice($o['total_price']) ?></td>
                        <td>
                            <a href="track-order.php?order=<?= $o['id'] ?>" class="icon-btn icon-btn-edit">
                                <span class="material-icons">search</span> Track
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="account-card">
            <h2 class="account-card-title">My Details</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><span class="material-icons" style="font-size:16px;vertical-align:middle;">check</span> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><span class="material-icons" style="font-size:16px;vertical-align:middle;">error</span> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="account.php?tab=profile">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name:</label>
                        <input class="form-input" type="text" name="first_name"
                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name:</label>
                        <input class="form-input" type="text" name="last_name"
                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address:</label>
                    <input class="form-input" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                           style="background:var(--gray-xlight);cursor:not-allowed;">
                    <div class="form-hint">Email cannot be changed.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address:</label>
                    <textarea class="form-input" name="address" rows="3"
                              placeholder="Your default delivery address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span> Save Changes
                </button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
