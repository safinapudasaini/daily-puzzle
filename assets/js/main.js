/**
 * Banana Quiz Game - Global Client Utilities
 * CIS046-3 Software For Enterprise
 * 
 * Provides base URL / CSRF meta extraction and notification lifecycle management.
 * Attribution: Built with AI pair-programming assistance.
 */

'use strict';

/**
 * Retrieve the current application base URL path from HTML head metadata.
 * @returns {string}
 */
function getAppBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta ? meta.getAttribute('content') : '/';
}

/**
 * Retrieve the active CSRF verification token from HTML head metadata.
 * @returns {string}
 */
function getAppCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Auto-dismiss alert notifications after 6 seconds
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 6000);
    });
});
