<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'greenfield_hub');

define('SITE_ROOT', realpath(__DIR__ . '/..'));

$_doc_root  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$_site_root = str_replace('\\', '/', SITE_ROOT);
$_base_path = substr($_site_root, strlen($_doc_root));
define('BASE_URL', rtrim(str_replace('\\', '/', $_base_path), '/') . '/');
unset($_doc_root, $_site_root, $_base_path);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('
    <div style="font-family:sans-serif;padding:40px;max-width:600px;margin:60px auto;
                background:#fff3f3;border:2px solid #e00;border-radius:8px;color:#900;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <hr style="border-color:#fcc;margin:16px 0;">
        <p style="font-size:.9rem;">
            Make sure:<br>
            1. XAMPP MySQL service is running<br>
            2. You have imported <strong>database.sql</strong> in phpMyAdmin<br>
            3. The database name is <strong>greenfield_hub</strong>
        </p>
    </div>');
}

$conn->set_charset('utf8mb4');

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isProducer(): bool {
    return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'producer';
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

function requireProducer(): void {
    requireLogin();
    if (!isProducer()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $message, string $type = 'info'): void {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;
}

function getBasketCount(): int {
    if (empty($_SESSION['basket'])) return 0;
    return array_sum(array_column($_SESSION['basket'], 'quantity'));
}

function getBasketTotal(): float {
    if (empty($_SESSION['basket'])) return 0.0;
    $total = 0.0;
    foreach ($_SESSION['basket'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function formatPrice(float $price): string {
    return '£' . number_format($price, 2);
}
