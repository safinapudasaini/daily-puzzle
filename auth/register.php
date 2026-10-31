<?php
/**
 * Banana Quiz Game - User Registration
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 4: Virtual Identity (Account creation, password hashing with password_hash())
 * - Secure input validation and duplicate identity checking
 * - Cross-Site Request Forgery (CSRF) token verification
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

init_session();

// If user is already logged in, redirect straight to dashboard
if (is_authenticated()) {
    redirect('dashboard/index.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $submittedCsrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submittedCsrf)) {
        $errors[] = 'Security verification failed (invalid CSRF token). Please refresh and try again.';
    }

    // 2. Extract & Sanitize Form Input
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    // 3. Server-Side Validation Rules
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Username must be between 3 and 30 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    // 4. Check for Duplicate Username or Email in Database
    if (empty($errors)) {
        try {
            $pdo = Database::getConnection();

            $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
            ]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                if (strcasecmp($existingUser['username'], $username) === 0) {
                    $errors[] = 'The username "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" is already taken. Please choose another.';
                }
                if (strcasecmp($existingUser['email'], $email) === 0) {
                    $errors[] = 'An account with this email address already exists. Please log in.';
                }
            } else {
                // 5. Hash Password using strong one-way algorithm (Bcrypt / Argon2 via PASSWORD_DEFAULT)
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare('INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())');
                $insertStmt->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $passwordHash,
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                // 6. Establish Authenticated Virtual Identity & Session Regeneration
                session_regenerate_id(true);
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                flash('success', 'Welcome, ' . $username . '! Your virtual identity has been registered successfully.');
                redirect('dashboard/index.php');
            }
        } catch (Exception $e) {
            error_log('[Registration Error] ' . $e->getMessage());
            $errors[] = 'A database error occurred while registering your account. Please try again.';
        }
    }
}

$pageTitle = 'Create Your Account';
$activePage = 'register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-card card">
    <div class="auth-header">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🍌✨</div>
        <h1>Create Account</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Establish your Virtual Identity to play and track scores.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <ul style="padding-left: 1.25rem; margin: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('auth/register.php') ?>" method="POST" novalidate id="register-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-input" 
                   value="<?= sanitize($username) ?>" required autocomplete="username"
                   placeholder="e.g. banana_champion">
            <div class="form-helper">3 to 30 characters (letters, numbers, underscores).</div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="<?= sanitize($email) ?>" required autocomplete="email"
                   placeholder="you@domain.com">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input" 
                   required autocomplete="new-password" placeholder="At least 8 characters">
            <div class="form-helper">Minimum 8 characters, with at least 1 letter & 1 number.</div>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                   required autocomplete="new-password" placeholder="Re-type your password">
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submit-btn" style="margin-top: 1.5rem;">
            Register Virtual Identity
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
        Already have an account? 
        <a href="<?= base_url('auth/login.php') ?>" style="color: var(--accent-dark); font-weight: 700; text-decoration: underline;">
            Log in here
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
