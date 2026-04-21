<?php
require_once 'includes/db.php';

if (isLoggedIn()) {
    redirect(BASE_URL . 'index.php');
}

$page_title = 'Sign Up';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']         ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?,?,?,?,'customer')");
            $stmt->bind_param('ssss', $first_name, $last_name, $email, $password);
            if ($stmt->execute()) {
                flash('Account created! Please log in.', 'success');
                redirect(BASE_URL . 'login.php');
            } else {
                $error = 'Something went wrong. Please try again.';
            }
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

        <h1 class="auth-title">Sign Up</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><span class="material-icons" style="font-size:16px;vertical-align:middle;">error</span> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="first_name">First Name:</label>
                <input class="form-input" type="text" id="first_name" name="first_name"
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                       placeholder="Jane" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="last_name">Last Name:</label>
                <input class="form-input" type="text" id="last_name" name="last_name"
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                       placeholder="Doe" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address:</label>
                <input class="form-input" type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="jane@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password:</label>
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="At least 6 characters" required>
                <div class="form-hint">Minimum 6 characters</div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:6px;">
                <span class="material-icons">person_add</span> Sign Up
            </button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="login.php" class="form-link">Log in here</a>
        </div>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
