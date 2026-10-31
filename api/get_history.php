<?php
/**
 * Banana Quiz Game - User History API Endpoint
 * CIS046-3 Software For Enterprise
 * 
 * Provides JSON history of completed games for the authenticated user.
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$userId = (int)$_SESSION['user_id'];

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('
        SELECT id, score, total_questions, correct_answers, accuracy, played_at
        FROM game_results
        WHERE user_id = :uid
        ORDER BY played_at DESC
    ');
    $stmt->execute([':uid' => $userId]);
    $records = $stmt->fetchAll();

    json_response([
        'success' => true,
        'count'   => count($records),
        'records' => $records,
    ]);
} catch (Exception $e) {
    error_log('[History API Error] ' . $e->getMessage());
    json_response([
        'success' => false,
        'error'   => 'Could not fetch history data.',
    ], 500);
}
