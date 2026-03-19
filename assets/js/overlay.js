/**
 * AuthMe — Overlay Controller
 *
 * Controls opening/closing the auth overlay, screen switching
 * (login ↔ register ↔ OTP), and binds all trigger links.
 *
 * The overlay uses position:fixed so it always covers the
 * entire viewport regardless of DOM placement.
 *
 * @package AuthMe
 * @since   1.1.0
 */

(function (window) {
    'use strict';

    var backdrop = null;
    var closeBtn = null;

    /* ── Open Overlay ────────────────────── */

    function authmeOpenOverlay() {
        backdrop = document.getElementById('authme-overlay-backdrop');
        if (!backdrop) return;

        // Show the backdrop
        backdrop.style.display = 'flex';

        // Lock body scrolling
        document.body.classList.add('authme-body-locked');

        // Force browser reflow before adding the visible class (for animation)
        void backdrop.offsetHeight;
        backdrop.classList.add('authme-overlay-visible');
    }

    /* ── Close Overlay ───────────────────── */

    function authmeCloseOverlay() {
        backdrop = document.getElementById('authme-overlay-backdrop');
        if (!backdrop) return;

        backdrop.classList.remove('authme-overlay-visible');

        // Wait for the transition to finish before hiding
        setTimeout(function () {
            backdrop.style.display = 'none';
            document.body.classList.remove('authme-body-locked');

            // Reset to the login screen
            authmeShowScreen('authme-login-screen');
            clearAllForms();

            // If URL has ?authme_open=1, remove it from the address bar
            // so closing and navigating doesn't re-trigger the popup
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                if (url.searchParams.has('authme_open')) {
                    url.searchParams.delete('authme_open');
                    window.history.replaceState({}, '', url.toString());
                }
            }
        }, 350);
    }

    /* ── Screen Switching ────────────────── */

    function authmeShowScreen(screenId) {
        var screens = document.querySelectorAll('.authme-screen');
        screens.forEach(function (screen) {
            screen.classList.remove('authme-screen-active');
        });

        var target = document.getElementById(screenId);
        if (target) {
            target.classList.add('authme-screen-active');
        }
    }

    /* ── Clear All Forms ─────────────────── */

    function clearAllForms() {
        // Reset all text/email/password inputs inside the overlay
        var container = document.getElementById('authme-overlay-container');
        if (!container) return;

        var inputs = container.querySelectorAll('input:not([type="checkbox"]):not([type="hidden"])');
        inputs.forEach(function (input) {
            input.value = '';
            // Reset password inputs to password type
            if (input.id && input.id.indexOf('password') !== -1) {
                input.type = 'password';
            }
            // Remove validation classes
            input.classList.remove('authme-input-success', 'authme-input-error', 'authme-otp-filled');
        });

        // Reset checkboxes
        var checkboxes = container.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function (cb) { cb.checked = false; });

        // Reset field messages
        var msgs = container.querySelectorAll('.authme-field-msg');
        msgs.forEach(function (msg) {
            msg.textContent = '';
            msg.className = 'authme-field-msg';
        });

        // Reset eye icons
        var toggleBtns = container.querySelectorAll('.authme-toggle-password');
        toggleBtns.forEach(function (btn) {
            var eyeOff = btn.querySelector('.authme-eye-off');
            var eyeOn  = btn.querySelector('.authme-eye-on');
            if (eyeOff) eyeOff.style.display = '';
            if (eyeOn)  eyeOn.style.display = 'none';
        });

        // Reset password strength bar
        var strengthContainer = document.getElementById('authme-reg-password-strength');
        if (strengthContainer) {
            strengthContainer.innerHTML = '';
        }

        // Disable submit buttons
        var btns = container.querySelectorAll('.authme-btn-primary');
        btns.forEach(function (btn) {
            if (btn.id === 'authme-otp-submit-btn') return; // OTP verify is always enabled
            btn.disabled = true;
        });
    }

    /* ── Event Listeners ─────────────────── */

    document.addEventListener('DOMContentLoaded', function () {

        backdrop = document.getElementById('authme-overlay-backdrop');
        closeBtn = document.getElementById('authme-overlay-close');

        // Close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', authmeCloseOverlay);
        }

        // Backdrop click (close when clicking outside the form)
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) {
                    authmeCloseOverlay();
                }
            });
        }

        // Escape key closes overlay
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                authmeCloseOverlay();
            }
        });

        // Screen switching links (data-screen attribute)
        var switchLinks = document.querySelectorAll('.authme-link[data-screen]');
        switchLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var targetScreen = this.getAttribute('data-screen');
                if (targetScreen) {
                    clearAllForms();
                    authmeShowScreen(targetScreen);
                }
            });
        });

        // Password toggle buttons
        var toggleBtns = document.querySelectorAll('.authme-toggle-password');
        toggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = this.getAttribute('data-target');
                var input    = document.getElementById(targetId);
                if (!input) return;

                var eyeOff = this.querySelector('.authme-eye-off');
                var eyeOn  = this.querySelector('.authme-eye-on');

                if (input.type === 'password') {
                    input.type = 'text';
                    if (eyeOff) eyeOff.style.display = 'none';
                    if (eyeOn)  eyeOn.style.display = '';
                } else {
                    input.type = 'password';
                    if (eyeOff) eyeOff.style.display = '';
                    if (eyeOn)  eyeOn.style.display = 'none';
                }
            });
        });

        // Bind any trigger links already on the page
        var triggers = document.querySelectorAll('.authme-trigger-link');
        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                authmeOpenOverlay();
            });
        });

    });

    // Expose globally
    window.authmeOpenOverlay  = authmeOpenOverlay;
    window.authmeCloseOverlay = authmeCloseOverlay;
    window.authmeShowScreen   = authmeShowScreen;

})(window);
