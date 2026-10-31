<?php
/**
 * Banana Quiz Game - User Logout
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 4: Virtual Identity (Session termination & destruction)
 * - Safe cookie purging and session invalidation
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

init_session();

// 1. Clear in-memory session data
$_SESSION = [];

// 2. Expire session cookie on browser client
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy server-side session file
session_destroy();

// 4. Start fresh transient session for the farewell flash notice
init_session();
flash('info', 'You have been logged out safely. Your virtual identity session is terminated.');

redirect('auth/login.php');
