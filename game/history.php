<?php
/**
 * Banana Quiz Game - User Game History
 * CIS046-3 Software For Enterprise
 * 
 * Displays historical quiz records for the authenticated virtual identity.
 * 
 * Demonstrates:
 * - Theme 4: Virtual Identity (Strict user-level data segregation)
 * - Enterprise Persistence: Querying MySQL relational records via PDO prepared statements
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$userId = (int)$_SESSION['user_id'];
$username = (string)$_SESSION['username'];

$games = [];
$totalGames = 0;
$totalScore = 0;
$totalQuestions = 0;
$totalCorrect = 0;
$lifetimeAccuracy = 0.0;

try {
    $pdo = Database::getConnection();

    // 1. Fetch all game results for the active virtual identity
    $stmt = $pdo->prepare('
        SELECT id, score, total_questions, correct_answers, accuracy, played_at
        FROM game_results
        WHERE user_id = :uid
        ORDER BY played_at DESC
    ');
    $stmt->execute([':uid' => $userId]);
    $games = $stmt->fetchAll();

    $totalGames = count($games);

    foreach ($games as $g) {
        $totalScore     += (int)$g['score'];
        $totalQuestions += (int)$g['total_questions'];
        $totalCorrect   += (int)$g['correct_answers'];
    }

    if ($totalQuestions > 0) {
        $lifetimeAccuracy = round(($totalCorrect / $totalQuestions) * 100, 1);
    }

} catch (Exception $e) {
    error_log('[History Page Error] ' . $e->getMessage());
    flash('error', 'Unable to retrieve game history from database.');
}

$pageTitle = 'My Game History';
$activePage = 'history';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">📜 Game History</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Records for Virtual Identity: <strong><?= sanitize($username) ?></strong>
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('dashboard/index.php') ?>" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>
        <a href="<?= base_url('game/play.php') ?>" class="btn btn-primary">
            🚀 Play New Game
        </a>
    </div>
</div>

<!-- Summary Metrics Bar -->
<section class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon">🎮</div>
        <div class="stat-content">
            <div class="stat-label">Total Rounds</div>
            <div class="stat-value"><?= number_format($totalGames) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⭐</div>
        <div class="stat-content">
            <div class="stat-label">Lifetime Points</div>
            <div class="stat-value"><?= number_format($totalScore) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
            <div class="stat-label">Overall Accuracy</div>
            <div class="stat-value"><?= $lifetimeAccuracy ?>%</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-label">Correct Ratio</div>
            <div class="stat-value"><?= number_format($totalCorrect) ?> <small style="font-size: 0.9rem; color: var(--text-muted);">/ <?= number_format($totalQuestions) ?></small></div>
        </div>
    </div>
</section>

<!-- History Table Card -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Completed Quiz Sessions</h2>
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            Showing <?= count($games) ?> <?= count($games) === 1 ? 'record' : 'records' ?>
        </span>
    </div>

    <?php if (empty($games)): ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🍌📝</div>
            <h3 style="font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No Game Records Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; max-width: 450px; margin-left: auto; margin-right: auto;">
                You haven't completed any banana puzzle rounds yet. Start a new game and test your mathematical picture skills!
            </p>
            <a href="<?= base_url('game/play.php') ?>" class="btn btn-primary btn-lg">
                Start First Game
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Round #</th>
                        <th>Score</th>
                        <th>Questions</th>
                        <th>Correct Answers</th>
                        <th>Accuracy</th>
                        <th>Performance Rating</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $idx => $game): ?>
                        <?php
                            $acc = (float)$game['accuracy'];
                            if ($acc >= 100.0) {
                                $ratingBadge = '<span class="badge badge-success">🌟 Perfect Score</span>';
                            } elseif ($acc >= 80.0) {
                                $ratingBadge = '<span class="badge badge-success">Great Job</span>';
                            } elseif ($acc >= 60.0) {
                                $ratingBadge = '<span class="badge badge-warning">Good Effort</span>';
                            } else {
                                $ratingBadge = '<span class="badge badge-danger">Needs Practice</span>';
                            }
                        ?>
                        <tr>
                            <td style="font-weight: 700;">#<?= (int)$game['id'] ?></td>
                            <td><strong style="color: var(--accent-dark);"><?= (int)$game['score'] ?></strong> pts</td>
                            <td><?= (int)$game['total_questions'] ?></td>
                            <td><?= (int)$game['correct_answers'] ?> / <?= (int)$game['total_questions'] ?></td>
                            <td><strong><?= number_format($acc, 1) ?>%</strong></td>
                            <td><?= $ratingBadge ?></td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                <?= date('Y-m-d H:i:s', strtotime($game['played_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
