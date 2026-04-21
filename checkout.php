<?php
require_once 'includes/db.php';
$page_title    = 'Checkout';
$basket        = $_SESSION['basket']        ?? [];
$delivery_type = $_SESSION['delivery_type'] ?? 'pickup';

if (empty($basket)) {
    flash('Your basket is empty.', 'error');
    redirect(BASE_URL . 'basket.php');
}

$total = getBasketTotal();

$user_address = '';
if (isLoggedIn()) {
    $u = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $u->bind_param('i', $_SESSION['user_id']);
    $u->execute();
    $user_address = $u->get_result()->fetch_assoc()['address'] ?? '';
}

require_once 'includes/header.php';
?>
<main>
<div class="container">
    <div style="padding:24px 0 10px;">
        <h1 style="font-size:1.7rem;font-weight:900;">Checkout</h1>
    </div>

    <div class="checkout-layout">

        <div>
            <div class="checkout-card" id="address-section"
                 style="<?= $delivery_type === 'pickup' ? 'display:none;' : '' ?>">
                <h2 class="checkout-heading"><span class="material-icons">location_on</span> Delivery Address</h2>
                <div class="form-group">
                    <label class="form-label" for="delivery_address">Full address:</label>
                    <textarea class="form-input" id="delivery_address" name="delivery_address"
                              rows="3" placeholder="e.g. 123 Main Street, Greenfield, GL1 2AB"><?= htmlspecialchars($user_address) ?></textarea>
                </div>
            </div>

            <div class="checkout-card">
                <h2 class="checkout-heading"><span class="material-icons">credit_card</span> Payment Details</h2>
                <p style="font-size:.82rem;color:var(--text-mid);margin-bottom:18px;">
                    <span class="material-icons" style="font-size:16px;vertical-align:middle;">info</span> This is a prototype — no real payment is taken.
                </p>

                <form action="checkout-process.php" method="POST" id="checkout-form">
                    <input type="hidden" name="delivery_type"    value="<?= htmlspecialchars($delivery_type) ?>">
                    <input type="hidden" name="delivery_address" id="hidden_address" value="<?= htmlspecialchars($user_address) ?>">

                    <div class="form-group">
                        <label class="form-label" for="card_name">Name on Card:</label>
                        <input class="form-input" type="text" id="card_name" name="card_name"
                               placeholder="Jane Doe"
                               value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : '' ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="card_number">Card Number:</label>
                        <input class="form-input" type="text" id="card_number" name="card_number"
                               placeholder="1234 5678 9012 3456"
                               maxlength="19" required
                               oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()">
                    </div>

                    <div class="card-grid">
                        <div class="form-group">
                            <label class="form-label" for="card_expiry">Expiry Date:</label>
                            <input class="form-input" type="text" id="card_expiry" name="card_expiry"
                                   placeholder="MM/YY" maxlength="5" required
                                   oninput="this.value=this.value.replace(/[^0-9/]/g,'')">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="card_cvv">CVV:</label>
                            <input class="form-input" type="text" id="card_cvv" name="card_cvv"
                                   placeholder="123" maxlength="3" required
                                   oninput="this.value=this.value.replace(/\D/g,'')">
                        </div>
                    </div>

                    <?php if (!isLoggedIn()): ?>
                    <div class="form-group">
                        <label class="form-label" for="guest_email">Email (for order updates):</label>
                        <input class="form-input" type="email" id="guest_email" name="guest_email"
                               placeholder="you@example.com">
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:6px;">
                        <span class="material-icons">lock</span> Place Order — <?= formatPrice($total) ?>
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="checkout-card">
                <h2 class="checkout-heading"><span class="material-icons">receipt</span> Order Summary</h2>

                <?php foreach ($basket as $item): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($item['name']) ?> &times;<?= $item['quantity'] ?></span>
                    <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                </div>
                <?php endforeach; ?>

                <div class="summary-row" style="padding-top:10px;">
                    <span style="color:var(--text-mid);">Delivery:</span>
                    <span style="text-transform:capitalize;"><?= htmlspecialchars($delivery_type) ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= formatPrice($total) ?></span>
                </div>

            </div>

            <div style="margin-top:12px;">
                <a href="basket.php" class="btn btn-outline btn-sm">
                    <span class="material-icons">arrow_back</span> Back to Basket
                </a>
            </div>
        </div>

    </div>
</div>
</main>

<script>
document.getElementById('checkout-form').addEventListener('submit', function() {
    const ta = document.getElementById('delivery_address');
    if (ta) {
        document.getElementById('hidden_address').value = ta.value;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
