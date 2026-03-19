/**
 * AuthMe — Register Form JavaScript
 *
 * Handles the registration flow:
 *   1. Real-time username availability check
 *   2. Real-time email availability check
 *   3. Password strength indicator
 *   4. Confirm password match
 *   5. "Send OTP" → stores user data, sends OTP, switches to OTP screen
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeIsValidEmail, authmeSetFieldState, authmeValidatePassword, authmePasswordStrength, authmeToast, authmeShowScreen */

(function () {
    'use strict';

    // Local state for the registration flow
    var regState = {
        usernameValid: false,
        emailValid: false,
        passwordValid: false,
        confirmValid: false,
    };

    /* ── Debounce Timers ─────────────────── */
    var usernameDebounce = null;
    var emailDebounce    = null;

    /* ── DOM Ready ───────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        var usernameInput  = document.getElementById('authme-reg-username');
        var emailInput     = document.getElementById('authme-reg-email');
        var passwordInput  = document.getElementById('authme-reg-password');
        var confirmInput   = document.getElementById('authme-reg-confirm-password');
        var submitBtn      = document.getElementById('authme-reg-submit-btn');
        var form           = document.getElementById('authme-register-form');

        if (!usernameInput || !emailInput || !passwordInput || !confirmInput || !submitBtn || !form) return;

        var usernameMsg  = document.getElementById('authme-reg-username-msg');
        var emailMsg     = document.getElementById('authme-reg-email-msg');
        var passwordMsg  = document.getElementById('authme-reg-password-msg');
        var confirmMsg   = document.getElementById('authme-reg-confirm-password-msg');
        var strengthEl   = document.getElementById('authme-reg-password-strength');

        /* ── Username Validation ─────────────── */
        usernameInput.addEventListener('input', function () {
            var value = this.value.trim();
            regState.usernameValid = false;
            updateSubmitButton();

            if (!value) {
                authmeSetFieldState(usernameInput, usernameMsg, '', '');
                return;
            }

            // Client-side checks first
            if (!/^[a-zA-Z]/.test(value)) {
                authmeSetFieldState(usernameInput, usernameMsg, 'error', 'Username must start with an alphabet character.');
                return;
            }
            if (!/^[a-zA-Z][a-zA-Z0-9]{2,19}$/.test(value)) {
                authmeSetFieldState(usernameInput, usernameMsg, 'error', 'Username must be 3–20 alphanumeric characters.');
                return;
            }

            // Debounced AJAX uniqueness check
            clearTimeout(usernameDebounce);
            usernameDebounce = setTimeout(function () {
                authmeAjax('authme_check_username', { username: value },
                    function (data) {
                        regState.usernameValid = true;
                        authmeSetFieldState(usernameInput, usernameMsg, 'success', data.message);
                        updateSubmitButton();
                    },
                    function (data) {
                        regState.usernameValid = false;
                        authmeSetFieldState(usernameInput, usernameMsg, 'error', data.message);
                        updateSubmitButton();
                    }
                );
            }, 500);
        });

        /* ── Email Validation ────────────────── */
        emailInput.addEventListener('input', function () {
            var value = this.value.trim();
            regState.emailValid = false;
            updateSubmitButton();

            if (!value) {
                authmeSetFieldState(emailInput, emailMsg, '', '');
                return;
            }

            // Client-side format check first
            if (!authmeIsValidEmail(value)) {
                authmeSetFieldState(emailInput, emailMsg, 'error', 'Please enter a valid email address.');
                return;
            }

            // Debounced AJAX uniqueness check
            clearTimeout(emailDebounce);
            emailDebounce = setTimeout(function () {
                authmeAjax('authme_check_email', { email: value },
                    function (data) {
                        regState.emailValid = true;
                        authmeSetFieldState(emailInput, emailMsg, 'success', data.message);
                        updateSubmitButton();
                    },
                    function (data) {
                        regState.emailValid = false;
                        authmeSetFieldState(emailInput, emailMsg, 'error', data.message);
                        updateSubmitButton();
                    }
                );
            }, 500);
        });

        /* ── Password Validation + Strength ──── */
        passwordInput.addEventListener('input', function () {
            var value = this.value;
            regState.passwordValid = false;

            // Update strength indicator
            updateStrengthBar(value);

            if (!value) {
                authmeSetFieldState(passwordInput, passwordMsg, '', '');
                updateSubmitButton();
                return;
            }

            var result = authmeValidatePassword(value);
            if (result.valid) {
                regState.passwordValid = true;
                authmeSetFieldState(passwordInput, passwordMsg, 'success', result.message);
            } else {
                authmeSetFieldState(passwordInput, passwordMsg, 'error', result.message);
            }

            // Re-validate confirm password if it has a value
            if (confirmInput.value) {
                validateConfirmPassword();
            }

            updateSubmitButton();
        });

        /* ── Confirm Password Validation ─────── */
        confirmInput.addEventListener('input', function () {
            validateConfirmPassword();
            updateSubmitButton();
        });

        function validateConfirmPassword() {
            var value = confirmInput.value;
            regState.confirmValid = false;

            if (!value) {
                authmeSetFieldState(confirmInput, confirmMsg, '', '');
                return;
            }

            if (value !== passwordInput.value) {
                authmeSetFieldState(confirmInput, confirmMsg, 'error', 'Passwords do not match.');
            } else {
                regState.confirmValid = true;
                authmeSetFieldState(confirmInput, confirmMsg, 'success', 'Passwords match.');
            }
        }

        /* ── Strength Bar ────────────────────── */
        function updateStrengthBar(password) {
            if (!strengthEl) return;

            if (!password) {
                strengthEl.innerHTML = '';
                return;
            }

            var level = authmePasswordStrength(password);

            // Create the bar element if not already present
            var bar = strengthEl.querySelector('.authme-strength-bar');
            if (!bar) {
                bar = document.createElement('div');
                bar.className = 'authme-strength-bar';
                strengthEl.innerHTML = '';
                strengthEl.appendChild(bar);
            }

            // Reset all level classes
            bar.className = 'authme-strength-bar authme-strength-' + level;
        }

        /* ── Submit Button State ─────────────── */
        function updateSubmitButton() {
            submitBtn.disabled = !(
                regState.usernameValid &&
                regState.emailValid &&
                regState.passwordValid &&
                regState.confirmValid
            );
        }

        /* ── Form Submission (Send OTP) ──────── */
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (submitBtn.disabled) return;

            var username = usernameInput.value.trim();
            var email    = emailInput.value.trim();
            var password = passwordInput.value;

            // Build user_data JSON
            var userData = JSON.stringify({
                username: username,
                email:    email,
                password: password,
            });

            // Disable button & show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending OTP…';

            // Send OTP for registration
            authmeAjax('authme_send_otp', {
                email:     email,
                purpose:   'registration',
                user_data: userData,
            },
            function (data) {
                authmeToast('success', data.message);

                // Store context for OTP screen
                document.getElementById('authme-otp-email').value     = email;
                document.getElementById('authme-otp-purpose').value   = 'registration';
                document.getElementById('authme-otp-user-data').value = userData;
                document.getElementById('authme-otp-user-id').value   = '';
                document.getElementById('authme-otp-remember').value  = document.getElementById('authme-reg-remember').checked ? 'true' : 'false';

                // Switch to OTP screen
                authmeShowScreen('authme-otp-screen');

                // Start the OTP timer
                if (typeof window.authmeStartOtpTimer === 'function') {
                    window.authmeStartOtpTimer();
                }

                // Focus the first OTP box
                var firstBox = document.querySelector('#authme-otp-screen .authme-otp-box');
                if (firstBox) firstBox.focus();

                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send OTP';
            },
            function (data) {
                authmeToast('error', data.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send OTP';
            });
        });

    });

})();
