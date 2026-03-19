/**
 * AuthMe — Toaster Notification System
 *
 * Provides authmeToast(type, message, duration) for showing
 * stacking, auto-dismissing toast notifications.
 *
 * Types: 'success', 'error', 'warning', 'info'
 *
 * @package AuthMe
 */

(function (window) {
    'use strict';

    // Icon map for toast types
    var iconMap = {
        success: '✓',
        error:   '✕',
        warning: '!',
        info:    'i',
    };

    /**
     * Show a toast notification.
     *
     * @param {string} type     'success' | 'error' | 'warning' | 'info'
     * @param {string} message  Text to display.
     * @param {number} duration Auto-dismiss time in ms (default: 5000).
     */
    function authmeToast(type, message, duration) {
        duration = duration || 5000;

        var container = document.getElementById('authme-toaster-container');
        if (!container) return;

        // Build toast element
        var toast = document.createElement('div');
        toast.className = 'authme-toast authme-toast-' + type;

        // Icon
        var icon = document.createElement('span');
        icon.className = 'authme-toast-icon';
        icon.textContent = iconMap[type] || 'i';
        toast.appendChild(icon);

        // Message
        var msg = document.createElement('span');
        msg.className = 'authme-toast-message';
        msg.textContent = message;
        toast.appendChild(msg);

        // Close button
        var closeBtn = document.createElement('button');
        closeBtn.className = 'authme-toast-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Close notification');
        closeBtn.addEventListener('click', function () {
            dismissToast(toast);
        });
        toast.appendChild(closeBtn);

        // Progress bar
        var progress = document.createElement('div');
        progress.className = 'authme-toast-progress';
        progress.style.animationDuration = duration + 'ms';
        toast.appendChild(progress);

        // Add to container
        container.appendChild(toast);

        // Auto-dismiss
        var timer = setTimeout(function () {
            dismissToast(toast);
        }, duration);

        // Store timer reference for potential cancellation
        toast._authmeTimer = timer;
    }

    /**
     * Dismiss a toast with animation.
     *
     * @param {HTMLElement} toast The toast element to dismiss.
     */
    function dismissToast(toast) {
        if (!toast || toast._authmeDismissing) return;
        toast._authmeDismissing = true;

        // Clear the auto-dismiss timer
        if (toast._authmeTimer) {
            clearTimeout(toast._authmeTimer);
        }

        // Add leaving animation class
        toast.classList.add('authme-toast-leaving');

        // Remove after animation
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Expose globally
    window.authmeToast = authmeToast;

})(window);
