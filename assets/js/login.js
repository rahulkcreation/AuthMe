/**
 * AuthMe — Login Form JavaScript
 *
 * Handles the login flow:
 *   1. User enters email/username → real-time lookup
 *   2. User enters password → credential validation
 *   3. "Send OTP" → validates creds, sends OTP, switches to OTP screen
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeIsValidEmail, authmeSetFieldState, authmeToast, authmeShowScreen */

(function () {
    'use strict';

    // Local state for the login flow
    var loginState = {
        identifierValid: false,
        passwordValid: false,
        userEmail: '',
        userId: '',
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
            loginState.userEmail = '';
            loginState.userId = '';
            updateSubmitButton();

            if (!value) {
                authmeSetFieldState(identifierInput, identifierMsg, '', '');
                return;
            }

            // Debounce the AJAX call
            clearTimeout(identifierDebounce);
            identifierDebounce = setTimeout(function () {
                authmeAjax('authme_check_user_exists', { identifier: value },
                    function (data) {
                        loginState.identifierValid = true;
                        loginState.userEmail = data.email || '';
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
            var value = this.value;
            loginState.passwordValid = value.length > 0;
            authmeSetFieldState(passwordInput, passwordMsg, '', '');
            updateSubmitButton();
        });

        /* ── Submit Button State ─────────────── */
        function updateSubmitButton() {
            submitBtn.disabled = !(loginState.identifierValid && loginState.passwordValid);
        }

        /* ── Form Submission (Send OTP) ──────── */
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (submitBtn.disabled) return;

            var identifier = identifierInput.value.trim();
            var password   = passwordInput.value;
            var remember   = document.getElementById('authme-login-remember').checked;

            // Disable button & show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying…';

            // Step 1: Validate credentials via AJAX
            authmeAjax('authme_login_user', {
                identifier: identifier,
                password: password,
                remember: remember ? 'true' : 'false',
            },
            function (data) {
                // Credentials valid → send OTP
                authmeToast('success', data.message);

                // Store login context for OTP screen
                document.getElementById('authme-otp-email').value      = data.email;
                document.getElementById('authme-otp-purpose').value    = 'login';
                document.getElementById('authme-otp-user-data').value  = '';
                document.getElementById('authme-otp-user-id').value    = data.user_id;
                document.getElementById('authme-otp-remember').value   = data.remember ? 'true' : 'false';

                // Step 2: Send OTP to user's email
                authmeAjax('authme_send_otp', {
                    email: data.email,
                    purpose: 'login',
                },
                function (otpData) {
                    authmeToast('success', otpData.message);
                    // Switch to OTP screen
                    authmeShowScreen('authme-otp-screen');
                    // Start the OTP timer (handled by otp.js)
                    if (typeof window.authmeStartOtpTimer === 'function') {
                        window.authmeStartOtpTimer();
                    }
                    // Focus the first OTP box
                    var firstBox = document.querySelector('#authme-otp-screen .authme-otp-box');
                    if (firstBox) firstBox.focus();
                },
                function (otpData) {
                    authmeToast('error', otpData.message);
                });

                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send OTP';
            },
            function (data) {
                authmeToast('error', data.message);
                authmeSetFieldState(passwordInput, passwordMsg, 'error', data.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send OTP';
            });
        });

    });

})();
