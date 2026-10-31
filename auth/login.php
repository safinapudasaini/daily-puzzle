<?php
/**
 * Banana Quiz Game - User Login
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 4: Virtual Identity (Authentication with password_verify() and session protection)
 * - Session fixation prevention via session_regenerate_id(true)
 * - Anti-enumeration security design
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

init_session();

// If user is already authenticated, redirect to dashboard
if (is_authenticated()) {
    redirect('dashboard/index.php');
}

$error = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $submittedCsrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submittedCsrf)) {
        $error = 'Security verification failed (invalid CSRF token). Please refresh and try again.';
    } else {
        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password   = (string)($_POST['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            $error = 'Please enter both your username/email and password.';
        } else {
            try {
                $pdo = Database::getConnection();

                // 2. Query user identity by either username or email
                $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = :ident OR email = :ident LIMIT 1');
                $stmt->execute([':ident' => $identifier]);
                $user = $stmt->fetch();

                // 3. Verify password hash using timing-safe password_verify()
                if ($user && password_verify($password, $user['password'])) {
                    // Prevent session fixation attack
                    session_regenerate_id(true);

                    $_SESSION['user_id']  = (int)$user['id'];
                    $_SESSION['username'] = (string)$user['username'];
                    $_SESSION['email']    = (string)$user['email'];

                    flash('success', 'Welcome back, ' . $user['username'] . '!');
                    redirect('dashboard/index.php');
                } else {
                    // Safe generic error prevents account enumeration
                    $error = 'Invalid credentials. Please verify your email/username and password.';
                }
            } catch (Exception $e) {
                error_log('[Login Error] ' . $e->getMessage());
                $error = 'A database error occurred during login. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Sign In';
$activePage = 'login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-card card">
    <div class="auth-header">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔐🍌</div>
        <h1>Welcome Back</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Authenticate your Virtual Identity to access your game dashboard.
        </p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert">
            <span><?= sanitize($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Viva Demonstration Quick Tip -->
    <div style="background-color: var(--primary-light); border: 1px dashed var(--primary); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.85rem; color: var(--accent-dark);">
        💡 <strong>Viva / Tutor Quick Demo Credentials:</strong><br>
        Username: <code>testuser</code> &bull; Password: <code>Password123!</code>
    </div>

    <form action="<?= base_url('auth/login.php') ?>" method="POST" novalidate id="login-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="identifier" class="form-label">Username or Email</label>
            <input type="text" id="identifier" name="identifier" class="form-input" 
                   value="<?= sanitize($identifier) ?>" required autocomplete="username"
                   placeholder="Enter your username or email">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input" 
                   required autocomplete="current-password" placeholder="Enter your password">
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-submit-btn" style="margin-top: 1.5rem;">
            Sign In to Virtual Identity
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Don't have an account yet? 
        <a href="<?= base_url('auth/register.php') ?>" style="color: var(--accent-dark); font-weight: 700; text-decoration: underline;">
            Register here
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
