<?php
require_once 'includes/db.php';

$action     = $_REQUEST['action']     ?? '';
$product_id = (int)($_REQUEST['product_id'] ?? 0);

if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

switch ($action) {

    case 'add':
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $stmt = $conn->prepare("SELECT id, name, price, image, stock_quantity FROM products WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();

        if ($p) {
            $existing = $_SESSION['basket'][$product_id]['quantity'] ?? 0;
            $new_qty  = min($existing + $qty, $p['stock_quantity']);

            $_SESSION['basket'][$product_id] = [
                'quantity' => $new_qty,
                'name'     => $p['name'],
                'price'    => (float)$p['price'],
                'image'    => $p['image'],
            ];
            flash('Added to basket!', 'success');
        }

        $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'shop.php';
        header('Location: ' . $back);
        exit;

    case 'increase':
        if (isset($_SESSION['basket'][$product_id])) {
            // make sure you cant add more than what is actually in stock
            $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_assoc()['stock_quantity'] ?? 999;
            if ($_SESSION['basket'][$product_id]['quantity'] < $stock) {
                $_SESSION['basket'][$product_id]['quantity']++;
            }
        }
        break;

    case 'decrease':
        if (isset($_SESSION['basket'][$product_id])) {
            $_SESSION['basket'][$product_id]['quantity']--;
            if ($_SESSION['basket'][$product_id]['quantity'] <= 0) {
                unset($_SESSION['basket'][$product_id]);
            }
        }
        break;

    case 'remove':
        unset($_SESSION['basket'][$product_id]);
        flash('Item removed from basket.', 'info');
        break;

    case 'delivery':
        $type = $_POST['delivery_type'] ?? 'pickup';
        if (in_array($type, ['pickup', 'delivery'])) {
            $_SESSION['delivery_type'] = $type;
        }
        break;
}

redirect(BASE_URL . 'basket.php');
