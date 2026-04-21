<?php
require_once '../includes/db.php';
requireProducer();

$page_title  = 'Producer Dashboard';
$producer_id = $_SESSION['user_id'];

// Stats
$active_orders = $conn->query(
    "SELECT COUNT(*) AS cnt FROM orders WHERE status IN ('ordered','processing')"
)->fetch_assoc()['cnt'];

$total_stock = $conn->prepare(
    "SELECT COALESCE(SUM(stock_quantity),0) AS total FROM products WHERE producer_id = ?"
);
$total_stock->bind_param('i', $producer_id);
$total_stock->execute();
$stock_count = $total_stock->get_result()->fetch_assoc()['total'];

$low_stock = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM products WHERE producer_id = ? AND stock_quantity < 10"
);
$low_stock->bind_param('i', $producer_id);
$low_stock->execute();
$low_stock_count = $low_stock->get_result()->fetch_assoc()['cnt'];

// Monthly sales for chart (last 6 months)
$monthly = $conn->query("
    SELECT DATE_FORMAT(o.created_at,'%b') AS month_name,
           DATE_FORMAT(o.created_at,'%Y-%m') AS month_key,
           COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.producer_id = 1
      AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month_key, month_name
    ORDER BY month_key ASC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Most popular products (by qty sold)
$popular = $conn->prepare("
    SELECT p.name, p.image, COALESCE(SUM(oi.quantity),0) AS sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.producer_id = ?
    GROUP BY p.id
    ORDER BY sold DESC
    LIMIT 6
");
$popular->bind_param('i', $producer_id);
$popular->execute();
$popular_products = $popular->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>
<main>
<div class="container">
<div class="producer-layout">

    <!-- Sidebar -->
    <?php include 'producer-sidebar.php'; ?>

    <!-- Main -->
    <div>
        <div style="margin-bottom:22px;">
            <h1 style="font-size:1.6rem;font-weight:900;">Dashboard</h1>
            <p class="text-muted text-small">Welcome back, <?= htmlspecialchars($_SESSION['user_first_name']) ?>. Here's an overview of your hub.</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Active Orders</div>
                <div class="stat-value"><?= $active_orders ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Items in Stock</div>
                <div class="stat-value"><?= $stock_count ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Low Stock</div>
                <div class="stat-value"><?= $low_stock_count ?></div>
            </div>
        </div>

        <!-- Chart + Popular -->
        <div class="dash-bottom">
            <!-- Sales Chart -->
            <div class="dash-card">
                <div class="dash-card-title">Sales Summary</div>
                <div class="chart-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Most Popular -->
            <div class="dash-card">
                <div class="dash-card-title">Most Popular Products</div>
                <div class="popular-grid">
                    <?php foreach ($popular_products as $pp): ?>
                    <div class="popular-item">
                        <img src="<?= htmlspecialchars('../' . $pp['image']) ?>"
                             alt="<?= htmlspecialchars($pp['name']) ?>">
                        <div class="popular-item-name"><?= htmlspecialchars($pp['name']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($monthly, 'month_name')) ?>,
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode(array_map('floatval', array_column($monthly, 'revenue'))) ?>,
            backgroundColor: '#4caf50',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: v => '£' + v }
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
