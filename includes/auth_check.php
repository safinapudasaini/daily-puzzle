<?php
/**
 * Banana Quiz Game - Authentication Gatekeeper
 * CIS046-3 Software For Enterprise
 * 
 * Enforces session-based authorization on protected endpoints.
 * Unauthenticated requests are rejected or redirected to login.
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

init_session();

if (!is_authenticated()) {
    // If request is an API AJAX request, return a JSON 401 Unauthorized
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
              || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
              || (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);

    if ($isAjax) {
        json_response([
            'success' => false,
            'error'   => 'Unauthorized. Please log in to continue.',
            'code'    => 401
        ], 401);
    }

    flash('error', 'Please log in to access this page.');
    redirect('auth/login.php');
}
