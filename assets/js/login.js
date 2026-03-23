/**
 * AuthMe — Login Form JavaScript
 *
 * Handles the login flow:
 *   1. User enters email/username → real-time account lookup
 *   2. User enters password
 *   3. "Login" → validates credentials server-side → logs in directly
 *      (No OTP step for login — OTP is only for Registration & Forgot Password)
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeSetFieldState, authmeToast */

(function () {
    'use strict';

    // Local state for the login flow
    var loginState = {
        identifierValid: false,
        passwordValid: false,
    };

    /* ── Debounce Utility ────────────────── */
    var identifierDebounce = null;

    /* ── DOM Ready ───────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        var identifierInput = document.getElementById('authme-login-identifier');
        var passwordInput   = document.getElementById('authme-login-password');
        var submitBtn       = document.getElementById('authme-login-submit-btn');
        var form            = document.getElementById('authme-login-form');

        if (!identifierInput || !passwordInput || !submitBtn || !form) return;

        var identifierMsg = document.getElementById('authme-login-identifier-msg');
        var passwordMsg   = document.getElementById('authme-login-password-msg');

        /* ── Identifier (username/email) Validation ── */
        identifierInput.addEventListener('input', function () {
            var value = this.value.trim();
            loginState.identifierValid = false;
            updateSubmitButton();

            if (!value) {
                authmeSetFieldState(identifierInput, identifierMsg, '', '');
                return;
            }

            // Debounce the AJAX call by 500ms
            clearTimeout(identifierDebounce);
            identifierDebounce = setTimeout(function () {
                authmeAjax('authme_check_user_exists', { identifier: value },
                    function (data) {
                        loginState.identifierValid = true;
                        authmeSetFieldState(identifierInput, identifierMsg, 'success', data.message);
                        updateSubmitButton();
                    },
                    function (data) {
                        loginState.identifierValid = false;
                        authmeSetFieldState(identifierInput, identifierMsg, 'error', data.message);
                        updateSubmitButton();
                    }
                );
            }, 500);
        });

        /* ── Password Validation ─────────────── */
        passwordInput.addEventListener('input', function () {
            loginState.passwordValid = this.value.length > 0;
            authmeSetFieldState(passwordInput, passwordMsg, '', '');
            updateSubmitButton();
        });

        /* ── Submit Button State ─────────────── */
        function updateSubmitButton() {
            submitBtn.disabled = !(loginState.identifierValid && loginState.passwordValid);
        }

        /* ── Form Submission (Direct Login) ──────── */
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (submitBtn.disabled) return;

            var identifier = identifierInput.value.trim();
            var password   = passwordInput.value;
            var remember   = document.getElementById('authme-login-remember').checked;

            // Disable button & show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in…';

            // Validate credentials AND set auth cookie in one AJAX call
            authmeAjax('authme_login_user', {
                identifier:  identifier,
                password:    password,
                remember:    remember ? 'true' : 'false',
                direct_login: 'true',  // Tells backend to set the auth cookie immediately
            },
            function (data) {
                authmeToast('success', data.message || 'Login successful!');
                // Reload page after short delay — user is now logged in
                setTimeout(function () {
                    window.location.reload();
                }, 900);
            },
            function (data) {
                authmeToast('error', data.message);
                authmeSetFieldState(passwordInput, passwordMsg, 'error', data.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            });
        });

    });

})();
