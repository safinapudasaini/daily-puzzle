<?php
/**
 * Banana Quiz Game - CSRF Protection Helper
 * CIS046-3 Software For Enterprise
 * 
 * Provides Cross-Site Request Forgery mitigation for state-changing HTTP requests.
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

/**
 * Generate or retrieve the current session's CSRF token.
 *
 * @return string
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

/**
 * Output a hidden HTML input containing the current CSRF token.
 *
 * @return string
 */
function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate a submitted CSRF token against the session token using timing-safe comparison.
 *
 * @param string|null $token Submitted token from form or header
 * @return bool
 */
function verify_csrf_token(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Extract CSRF token from either POST array, JSON payload, or HTTP headers.
 *
 * @return string|null
 */
function get_submitted_csrf_token(): ?string
{
    // 1. Check POST form parameter
    if (!empty($_POST['csrf_token'])) {
        return (string)$_POST['csrf_token'];
    }

    // 2. Check X-CSRF-Token HTTP header
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    // 3. Check JSON request body if present
    $input = file_get_contents('php://input');
    if ($input) {
        $json = json_decode($input, true);
        if (is_array($json) && !empty($json['csrf_token'])) {
            return (string)$json['csrf_token'];
        }
    }

    return null;
}
