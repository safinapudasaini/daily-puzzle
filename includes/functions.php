<?php
/**
 * Banana Quiz Game - Core Helper Functions
 * CIS046-3 Software For Enterprise
 * 
 * Provides session initialization, URL resolution, sanitization, flash messages,
 * and JSON response utilities.
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

/**
 * Initialize a secure PHP session if not already active.
 */
function init_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce secure session cookie settings
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path'     => '/',
            'domain'   => $cookieParams['domain'],
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Calculate the project root web path dynamically.
 * Works seamlessly whether hosted at root (http://localhost:8000)
 * or in a subfolder (http://localhost/banana_game or http://localhost/Daily%20puzzle).
 *
 * @param string $path Optional relative path to append
 * @return string Clean resolved web URL path
 */
function base_url(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Check known first-level subdirectories of the application
    $subdirs = ['/api/', '/auth/', '/dashboard/', '/game/', '/includes/', '/tests/'];
    $projectRoot = '';

    foreach ($subdirs as $subdir) {
        $pos = strpos($scriptName, $subdir);
        if ($pos !== false) {
            $projectRoot = substr($scriptName, 0, $pos);
            break;
        }
    }

    if ($projectRoot === '') {
        $projectRoot = rtrim(dirname($scriptName), '/\\');
    }

    $projectRoot = rtrim($projectRoot, '/');
    $cleanPath = ltrim($path, '/');

    if ($cleanPath === '') {
        return $projectRoot === '' ? '/' : $projectRoot . '/';
    }

    return ($projectRoot === '' ? '' : $projectRoot) . '/' . $cleanPath;
}

/**
 * Sanitize user input for safe HTML output (prevents Cross-Site Scripting - XSS).
 *
 * @param string|null $data
 * @return string
 */
function sanitize(?string $data): string
{
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check whether the current visitor is authenticated.
 *
 * @return bool
 */
function is_authenticated(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }
    return !empty($_SESSION['user_id']);
}

/**
 * Return currently authenticated user data, or null.
 *
 * @return array<string, mixed>|null
 */
function current_user(): ?array
{
    if (!is_authenticated()) {
        return null;
    }

    return [
        'id'       => (int)$_SESSION['user_id'],
        'username' => (string)($_SESSION['username'] ?? 'User'),
        'email'    => (string)($_SESSION['email'] ?? ''),
    ];
}

/**
 * Set or retrieve flash messages.
 *
 * @param string $type Message category: 'success', 'error', 'info', 'warning'
 * @param string|null $message If provided, stores flash message. If null, returns all for that type.
 * @return array<string>
 */
function flash(string $type, ?string $message = null): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    if ($message !== null) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        if (!isset($_SESSION['flash_messages'][$type])) {
            $_SESSION['flash_messages'][$type] = [];
        }
        $_SESSION['flash_messages'][$type][] = $message;
        return [];
    }

    $messages = $_SESSION['flash_messages'][$type] ?? [];
    unset($_SESSION['flash_messages'][$type]);
    return $messages;
}

/**
 * Get and clear all flash messages across all types.
 *
 * @return array<string, array<string>>
 */
function get_all_flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        init_session();
    }

    $messages = $_SESSION['flash_messages'] ?? [];
    $_SESSION['flash_messages'] = [];
    return $messages;
}

/**
 * Send a standardized JSON response and exit.
 *
 * @param array<string, mixed> $data
 * @param int $statusCode HTTP status code
 * @return never
 */
function json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Perform safe HTTP redirection.
 *
 * @param string $path Target URL or relative path
 * @return never
 */
function redirect(string $path): void
{
    // If not an absolute URL with scheme, route via base_url
    if (!preg_match('#^https?://#i', $path)) {
        $path = base_url($path);
    }
    header('Location: ' . $path);
    exit;
}
