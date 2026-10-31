<?php
/**
 * Banana Quiz Game - Reset Active Game Round
 * CIS046-3 Software For Enterprise
 * 
 * Clears in-memory session quiz state so a fresh round can begin.
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['game']);

json_response([
    'success' => true,
    'message' => 'Game round has been reset successfully.'
]);
