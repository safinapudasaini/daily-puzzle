<?php
/**
 * Banana Quiz Game - Public Landing Page
 * CIS046-3 Software For Enterprise
 * 
 * Introduces the application, demonstrates the four software engineering themes,
 * and explains the distributed client-server architecture.
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

init_session();
$currentUser = current_user();

$pageTitle = 'Banana Quiz Game - Enterprise Distributed Application';
$activePage = 'home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section style="text-align: center; padding: 3rem 1rem 2rem;">
    <div style="font-size: 3.5rem; margin-bottom: 0.5rem; animation: float 3s ease-in-out infinite;">🍌🧩⚡</div>
    <h1 style="font-size: 2.75rem; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-bottom: 1rem;">
        The Banana Quiz Game
    </h1>
    <p style="font-size: 1.2rem; color: var(--text-muted); max-width: 720px; margin: 0 auto 2rem; line-height: 1.6;">
        A high-performance distributed web application integrating Marc Conrad's official Banana API.
        Solve picture-based mathematical equations, track your accuracy, and test your enterprise computing knowledge.
    </p>

    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <?php if ($currentUser): ?>
            <a href="<?= base_url('game/play.php') ?>" class="btn btn-primary btn-lg">
                🎮 Play Game Now
            </a>
            <a href="<?= base_url('dashboard/index.php') ?>" class="btn btn-secondary btn-lg">
                📊 Go to Dashboard
            </a>
        <?php else: ?>
            <a href="<?= base_url('auth/register.php') ?>" class="btn btn-primary btn-lg">
                🚀 Create Virtual Identity
            </a>
            <a href="<?= base_url('auth/login.php') ?>" class="btn btn-secondary btn-lg">
                🔐 Sign In
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- The Four Academic Assignment Themes Section -->
<section style="margin: 3.5rem 0;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main);">Enterprise Themes Demonstrated</h2>
        <p style="color: var(--text-muted); font-size: 1rem;">
            Core software engineering competencies implemented across this application (CIS046-3).
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
        <!-- Theme 1 -->
        <div class="card" style="border-top: 4px solid #3B82F6;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌿</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">1. Version Control</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                Managed using Git and GitHub. Structured feature branches (<code>main</code>, <code>feature/banana-game-core</code>), atomic commits, and a standardized <code>.gitignore</code> ensure auditable development tracking.
            </p>
        </div>

        <!-- Theme 2 -->
        <div class="card" style="border-top: 4px solid #10B981;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔄</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">2. Interoperability</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                Heterogeneous system communication: Frontend communicates asynchronously with PHP backend via JSON/Fetch API, while PHP server integrates with external Banana API via HTTP/REST cURL requests.
            </p>
        </div>

        <!-- Theme 3 -->
        <div class="card" style="border-top: 4px solid #F59E0B;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⚡</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">3. Event-Driven UI</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                Interactive user interface decoupled using JavaScript <code>addEventListener()</code> for clicks, keyboard input, keypad triggers, and asynchronous network state transitions without page reloading.
            </p>
        </div>

        <!-- Theme 4 -->
        <div class="card" style="border-top: 4px solid #8B5CF6;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">👤</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">4. Virtual Identity</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                Enterprise identity management: Secure registration, Bcrypt password hashing (<code>password_hash()</code>), timing-safe verification, PHP session cookies, CSRF protection, and strict user-scoped data access.
            </p>
        </div>
    </div>
</section>

<!-- Distributed Architecture Overview -->
<section class="card" style="margin-bottom: 3rem; background: linear-gradient(to bottom, #FFFFFF, #F8FAFC);">
    <div class="card-header">
        <h2 class="card-title">🌐 Distributed Client-Server Architecture</h2>
        <span class="badge badge-info">Multi-Tier Integration</span>
    </div>
    <div style="font-size: 0.95rem; color: var(--text-main); line-height: 1.7;">
        <p style="margin-bottom: 1rem;">
            This system implements a <strong>distributed client-server architecture</strong> across four distinct, communicating nodes:
        </p>
        <ol style="padding-left: 1.5rem; margin-bottom: 1.5rem; color: var(--text-muted);">
            <li><strong>Frontend Client Tier:</strong> Browser environment running responsive HTML5, CSS3, and JavaScript Fetch API for event processing and UI rendering.</li>
            <li><strong>Application Server Tier:</strong> Apache/PHP 8+ server managing business logic, session security, authentication, and serving as a secure proxy to external APIs.</li>
            <li><strong>Enterprise Persistence Tier:</strong> MySQL database storing virtual identities, hashed passwords, and historical game telemetry using PDO prepared statements.</li>
            <li><strong>External Cloud API Service:</strong> Marc Conrad's Banana API (<code>marcconrad.com</code>), an independent web service dynamically generating mathematical puzzle equations.</li>
        </ol>
        <div style="background: var(--primary-light); border-left: 4px solid var(--primary); padding: 0.75rem 1rem; border-radius: var(--radius-sm); font-size: 0.85rem; color: var(--accent-dark);">
            🔒 <strong>Enterprise Security Guarantee:</strong> The frontend never communicates directly with the external Banana API, preventing solution exposure and client-side score tampering.
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
