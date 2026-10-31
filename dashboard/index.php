<?php
/**
 * Banana Quiz Game - User Dashboard
 * CIS046-3 Software For Enterprise
 * 
 * Aggregates player performance metrics from MySQL and provides navigation
 * to quiz gameplay and game history.
 * 
 * Demonstrates:
 * - Theme 4: Virtual Identity (Role/User-specific data scoping via PDO prepared statements)
 * - Distributed Architecture (Presentation tier querying enterprise persistence tier)
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$userId = (int)$_SESSION['user_id'];
$username = (string)$_SESSION['username'];

$totalGames = 0;
$totalQuestions = 0;
$totalCorrect = 0;
$overallAccuracy = 0.0;
$highestScore = 0;
$recentGames = [];

try {
    $pdo = Database::getConnection();

    // 1. Aggregate Statistics for this specific user
    $statsStmt = $pdo->prepare('
        SELECT 
            COUNT(*) AS total_games,
            COALESCE(SUM(total_questions), 0) AS total_questions,
            COALESCE(SUM(correct_answers), 0) AS total_correct,
            COALESCE(MAX(score), 0) AS highest_score
        FROM game_results 
        WHERE user_id = :uid
    ');
    $statsStmt->execute([':uid' => $userId]);
    $stats = $statsStmt->fetch();

    if ($stats) {
        $totalGames     = (int)$stats['total_games'];
        $totalQuestions = (int)$stats['total_questions'];
        $totalCorrect   = (int)$stats['total_correct'];
        $highestScore   = (int)$stats['highest_score'];

        if ($totalQuestions > 0) {
            $overallAccuracy = round(($totalCorrect / $totalQuestions) * 100, 1);
        }
    }

    // 2. Fetch Recent 5 Game Results
    $historyStmt = $pdo->prepare('
        SELECT id, score, total_questions, correct_answers, accuracy, played_at
        FROM game_results
        WHERE user_id = :uid
        ORDER BY played_at DESC
        LIMIT 5
    ');
    $historyStmt->execute([':uid' => $userId]);
    $recentGames = $historyStmt->fetchAll();

} catch (Exception $e) {
    error_log('[Dashboard Stats Error] ' . $e->getMessage());
    flash('error', 'Unable to retrieve your complete game statistics. Please refresh.');
}

$pageTitle = 'Player Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<section class="welcome-banner">
    <div>
        <h1 class="welcome-title">Welcome back, <?= sanitize($username) ?>! 👋</h1>
        <p style="font-size: 1.05rem; opacity: 0.95; max-width: 600px;">
            Ready to challenge your brain? Solve picture-based mathematical equations powered by the official Banana API.
        </p>
    </div>
    <div>
        <a href="<?= base_url('game/play.php') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(0,0,0,0.2);">
            🚀 Start New Game
        </a>
    </div>
</section>

<!-- Performance Statistics Cards -->
<section class="stats-grid" aria-label="Player Performance Summary">
    <div class="stat-card">
        <div class="stat-icon">🎮</div>
        <div class="stat-content">
            <div class="stat-label">Games Played</div>
            <div class="stat-value"><?= number_format($totalGames) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-content">
            <div class="stat-label">High Score</div>
            <div class="stat-value"><?= number_format($highestScore) ?> <span style="font-size: 0.9rem; color: var(--text-muted);">pts</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <div class="stat-label">Correct Answers</div>
            <div class="stat-value"><?= number_format($totalCorrect) ?> <span style="font-size: 0.9rem; color: var(--text-muted);">/ <?= number_format($totalQuestions) ?></span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
            <div class="stat-label">Overall Accuracy</div>
            <div class="stat-value"><?= $overallAccuracy ?>%</div>
        </div>
    </div>
</section>

<!-- Recent Games & Quick Actions -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Recent Activity Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Game History</h2>
            <a href="<?= base_url('game/history.php') ?>" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                View All Records &rarr;
            </a>
        </div>

        <?php if (empty($recentGames)): ?>
            <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🍌</div>
                <p style="font-weight: 600;">No games played yet!</p>
                <p style="font-size: 0.9rem; margin-top: 0.25rem;">Click "Start New Game" above to begin your first quiz.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Round #</th>
                            <th>Score</th>
                            <th>Correct</th>
                            <th>Accuracy</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentGames as $game): ?>
                            <?php
                                $acc = (float)$game['accuracy'];
                                $badgeClass = $acc >= 80 ? 'badge-success' : ($acc >= 50 ? 'badge-warning' : 'badge-danger');
                            ?>
                            <tr>
                                <td style="font-weight: 700;">#<?= (int)$game['id'] ?></td>
                                <td><strong><?= (int)$game['score'] ?></strong> pts</td>
                                <td><?= (int)$game['correct_answers'] ?> / <?= (int)$game['total_questions'] ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= number_format($acc, 1) ?>%</span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    <?= date('M j, Y, g:i a', strtotime($game['played_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- How It Works / Assignment Guide Panel -->
    <div class="card" style="background-color: #FAFAFA;">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.1rem;">🕹️ How to Play</h3>
        </div>
        <ul style="padding-left: 1.25rem; font-size: 0.9rem; color: var(--text-muted); line-height: 1.7;">
            <li>Each round consists of <strong>5 mathematical puzzles</strong>.</li>
            <li>Study the equation images generated live by the <strong>Banana API</strong>.</li>
            <li>Find the numeric value of the final missing element (digits <strong>0–9</strong>).</li>
            <li>Enter your answer via on-screen keypad or keyboard.</li>
            <li>Score points for every correct calculation!</li>
        </ul>

        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <a href="<?= base_url('game/play.php') ?>" class="btn btn-primary btn-block">
                Start Playing Now
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
