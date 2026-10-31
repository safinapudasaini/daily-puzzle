<?php
/**
 * Banana Quiz Game - Common Footer Component
 * CIS046-3 Software For Enterprise
 * 
 * Displays assignment academic indicators and loads common client scripts.
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);
?>
</main>

<footer class="footer">
    <div class="footer-container">
        <div>
            <strong>Banana Quiz Game</strong> &bull; CIS046-3 Software For Enterprise &bull; Distributed Web Application
            <div style="margin-top: 0.35rem; font-size: 0.8rem; color: var(--text-light);">
                Demonstrating: 
                <span class="theme-pill">1. Version Control</span>
                <span class="theme-pill">2. Interoperability</span>
                <span class="theme-pill">3. Event-Driven Programming</span>
                <span class="theme-pill">4. Virtual Identity</span>
            </div>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted); text-align: right;">
            Powered by the <a href="https://marcconrad.com/uob/banana/doc.php" target="_blank" rel="noopener" style="color: var(--accent-dark); text-decoration: underline;">Official Banana API</a>
        </div>
    </div>
</footer>

<!-- Global Application Script -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
