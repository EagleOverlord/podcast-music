<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'basket.php');
}

$basket = $_SESSION['basket'] ?? [];
if (empty($basket)) {
    flash('Your basket is empty.', 'error');
    redirect(BASE_URL . 'basket.php');
}

$card_name   = trim($_POST['card_name']   ?? '');
$card_number = preg_replace('/\s/', '', $_POST['card_number'] ?? '');
$card_expiry = trim($_POST['card_expiry'] ?? '');
$card_cvv    = trim($_POST['card_cvv']    ?? '');
$delivery_type    = $_POST['delivery_type']    ?? 'pickup';
$delivery_address = trim($_POST['delivery_address'] ?? '');

if (!$card_name || strlen($card_number) < 12 || !$card_expiry || strlen($card_cvv) < 3) {
    flash('Please complete all payment fields.', 'error');
    redirect(BASE_URL . 'checkout.php');
}

if ($delivery_type === 'delivery' && empty($delivery_address)) {
    flash('Please enter a delivery address.', 'error');
    redirect(BASE_URL . 'checkout.php');
}

$customer_id = $_SESSION['user_id'] ?? null;

if (!$customer_id) {
    $guest_email = trim($_POST['guest_email'] ?? 'guest@greenfield.com');
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $guest_email);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $customer_id = $existing['id'];
    } else {
        $ins = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Guest','User',?,'guest','customer')");
        $ins->bind_param('s', $guest_email);
        $ins->execute();
        $customer_id = $conn->insert_id;
    }
}

$total = getBasketTotal();

$order_stmt = $conn->prepare(
    "INSERT INTO orders (customer_id, status, delivery_type, total_price, delivery_address, card_name)
     VALUES (?, 'ordered', ?, ?, ?, ?)"
);
$order_stmt->bind_param('isdss', $customer_id, $delivery_type, $total, $delivery_address, $card_name);
$order_stmt->execute();
$order_id = $conn->insert_id;

$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
foreach ($basket as $product_id => $item) {
    $item_stmt->bind_param('iiid', $order_id, $product_id, $item['quantity'], $item['price']);
    $item_stmt->execute();

    $conn->query("UPDATE products SET stock_quantity = GREATEST(stock_quantity - {$item['quantity']}, 0) WHERE id = $product_id");
}

unset($_SESSION['basket'], $_SESSION['delivery_type']);
$_SESSION['last_order_id'] = $order_id;

flash('Order placed successfully! Thank you, ' . htmlspecialchars($card_name) . '.', 'success');
redirect(BASE_URL . 'order-confirmation.php?order=' . $order_id);
