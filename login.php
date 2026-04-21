<?php
require_once 'includes/db.php';

// if the user is already logged in, send them to the right page
if (isLoggedIn()) {
    redirect(BASE_URL . (isProducer() ? 'producer/dashboard.php' : 'account.php'));
}

$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // compare the password directly - no encryption needed for this project
        if ($user && $password === $user['password']) {
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['user_email']      = $user['email'];
            $_SESSION['user_name']       = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_first_name'] = $user['first_name'];
            $_SESSION['user_role']       = $user['role'];

            flash('Welcome back, ' . $user['first_name'] . '!', 'success');

            $dest = $_GET['redirect'] ?? '';
            if ($user['role'] === 'producer') {
                redirect(BASE_URL . 'producer/dashboard.php');
            } else {
                redirect(BASE_URL . ($dest ?: 'index.php'));
            }
        } else {
            $error = 'Incorrect email or password. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>
<main>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>assets/logo.png" alt="Logo">
            <span class="auth-logo-name">Greenfield Local Hub</span>
        </div>

        <h1 class="auth-title">Login</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><span class="material-icons" style="font-size:16px;vertical-align:middle;">⚠️</span> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">Email Address:</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password:</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="Your password" required>
            </div>

            <div class="forgot-row">
                <a href="#" class="form-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                <span class="material-icons">🔒</span> Login
            </button>
        </form>

        <div class="form-footer">
            Don't have an account? <a href="signup.php" class="form-link">Sign up here</a>
        </div>

        <div style="margin-top:20px;padding:14px;background:var(--green-light);border-radius:var(--radius-sm);font-size:.82rem;">
            <strong>Demo accounts:</strong><br>
            Customer: jane@example.com / customer123<br>
            Producer: producer@greenfield.com / producer123
        </div>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
