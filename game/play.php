<?php
/**
 * Banana Quiz Game - Main Quiz Gameplay Interface
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 3: Event-Driven Programming (DOM event listeners, asynchronous UI)
 * - Distributed Client Presentation Tier
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Play Banana Quiz';
$activePage = 'play';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="game-container">
    <!-- Game HUD Header -->
    <div class="game-hud" id="game-hud">
        <div class="hud-item">
            <span class="hud-label">Progress</span>
            <span class="hud-value"><span id="hud-question">1</span> / <span id="hud-total">5</span></span>
        </div>
        <div class="hud-item">
            <span class="hud-label">Current Score</span>
            <span class="hud-value"><span id="hud-score">0</span> <small style="font-size: 0.8rem; color: var(--text-muted);">pts</small></span>
        </div>
        <div class="hud-item">
            <span class="hud-label">Correct</span>
            <span class="hud-value"><span id="hud-correct">0</span></span>
        </div>
        <div>
            <button type="button" class="btn btn-secondary" id="btn-restart-round" style="font-size: 0.85rem; padding: 0.45rem 0.85rem;">
                🔄 Restart Round
            </button>
        </div>
    </div>

    <!-- Active Puzzle Card -->
    <div class="puzzle-card card" id="puzzle-card">
        <!-- API Loading Indicator -->
        <div id="puzzle-loading" style="display: none; padding: 3rem 1rem;">
            <div class="spinner"></div>
            <p style="color: var(--text-muted); font-weight: 600;">Fetching puzzle from official Banana API...</p>
        </div>

        <!-- API Error & Retry Panel -->
        <div id="puzzle-error" style="display: none; padding: 2rem 1rem;">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚠️</div>
            <h3 style="color: var(--danger); margin-bottom: 0.5rem;">External API Error</h3>
            <p id="puzzle-error-message" style="color: var(--text-muted); margin-bottom: 1.25rem;">
                Unable to contact the Banana API server.
            </p>
            <button type="button" class="btn btn-primary" id="btn-retry-puzzle">
                🔄 Retry Connection
            </button>
        </div>

        <!-- Puzzle Content Display -->
        <div id="puzzle-content">
            <div class="puzzle-image-wrapper">
                <img id="puzzle-img" src="" alt="Banana Mathematical Puzzle" style="display: none;">
            </div>

            <!-- Answer Input Controls -->
            <div id="input-controls">
                <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                    Find the missing number for the final equation (<strong>0–9</strong>):
                </p>

                <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <input type="text" id="answer-display" readonly class="form-input" 
                           style="width: 100px; text-align: center; font-size: 1.8rem; font-weight: 800; color: var(--accent-dark); background-color: var(--primary-light); border-color: var(--primary);" 
                           placeholder="?" aria-label="Selected Answer">
                </div>

                <!-- On-Screen Numeric Keypad (Event-Driven Touch/Click Friendly) -->
                <div class="keypad-container" id="keypad">
                    <button type="button" class="key-btn" data-val="1">1</button>
                    <button type="button" class="key-btn" data-val="2">2</button>
                    <button type="button" class="key-btn" data-val="3">3</button>
                    <button type="button" class="key-btn" data-val="4">4</button>
                    <button type="button" class="key-btn" data-val="5">5</button>
                    <button type="button" class="key-btn" data-val="6">6</button>
                    <button type="button" class="key-btn" data-val="7">7</button>
                    <button type="button" class="key-btn" data-val="8">8</button>
                    <button type="button" class="key-btn" data-val="9">9</button>
                    <button type="button" class="key-btn" data-val="0">0</button>
                </div>

                <div class="answer-actions">
                    <button type="button" class="btn btn-secondary" id="btn-clear-answer" style="flex: 1;">
                        Clear
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-submit-answer" style="flex: 2;">
                        Submit Answer &rarr;
                    </button>
                </div>
            </div>

            <!-- Interactive Feedback Box -->
            <div class="feedback-box" id="feedback-box">
                <div id="feedback-text"></div>
                <button type="button" class="btn btn-primary" id="btn-next-question" style="margin-top: 1rem; display: none;">
                    Next Puzzle &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Game Over Card (Hidden until round concludes) -->
    <div class="card game-over-card" id="game-over-card" style="display: none;">
        <div class="result-trophy">🏆</div>
        <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-main);">Quiz Round Completed!</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.05rem;">
            Your results have been calculated on the server and saved to MySQL.
        </p>

        <div class="stats-grid" style="max-width: 600px; margin: 0 auto 2rem;">
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-content">
                    <div class="stat-label">Final Score</div>
                    <div class="stat-value" id="final-score-val">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-content">
                    <div class="stat-label">Accuracy</div>
                    <div class="stat-value" id="final-acc-val">0%</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-label">Correct</div>
                    <div class="stat-value"><span id="final-correct-val">0</span> / 5</div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary btn-lg" id="btn-play-again">
                🍌 Play Another Round
            </button>
            <a href="<?= base_url('dashboard/index.php') ?>" class="btn btn-secondary btn-lg">
                📊 View Dashboard
            </a>
            <a href="<?= base_url('game/history.php') ?>" class="btn btn-secondary btn-lg">
                📜 Full History
            </a>
        </div>
    </div>
</div>

<!-- Load Quiz Game Event Controller -->
<script src="<?= base_url('assets/js/game.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
