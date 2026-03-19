/**
 * AuthMe — OTP Verification JavaScript
 *
 * Handles:
 *   - 6-digit OTP box auto-tabbing + paste support
 *   - 60-second countdown timer
 *   - Resend OTP logic
 *   - Verify & Proceed → finalizes registration or login
 *
 * @package AuthMe
 * @since   1.0.0
 */

/* global authmeAjax, authmeToast, authmeCloseOverlay */

(function (window) {
    'use strict';

    var OTP_DURATION = 60; // seconds
    var timeLeft     = OTP_DURATION;
    var timerId      = null;

    /* ── DOM Ready ───────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        var otpBoxes    = document.querySelectorAll('#authme-otp-screen .authme-otp-box');
        var otpForm     = document.getElementById('authme-otp-form');
        var resendBtn   = document.getElementById('authme-resend-btn');
        var submitBtn   = document.getElementById('authme-otp-submit-btn');

        if (!otpForm || otpBoxes.length === 0) return;

        /* ── OTP Box Input Handling ──────────── */
        otpBoxes.forEach(function (box, index) {

            // Handle input — only allow digits, auto-tab forward
            box.addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                // Add filled class for visual feedback
                if (e.target.value) {
                    e.target.classList.add('authme-otp-filled');
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('authme-otp-filled');
                }
            });

            // Handle backspace — move to previous box
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpBoxes[index - 1].focus();
                    otpBoxes[index - 1].value = '';
                    otpBoxes[index - 1].classList.remove('authme-otp-filled');
                }
            });

            // Handle paste — fill all boxes
            box.addEventListener('paste', function (e) {
                e.preventDefault();
                var pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                if (pastedData) {
                    for (var i = 0; i < pastedData.length; i++) {
                        if (otpBoxes[i]) {
                            otpBoxes[i].value = pastedData[i];
                            otpBoxes[i].classList.add('authme-otp-filled');
                            if (i < otpBoxes.length - 1) {
                                otpBoxes[i + 1].focus();
                            } else {
                                otpBoxes[i].focus();
                            }
                        }
                    }
                }
            });
        });

        /* ── Form Submit (Verify & Proceed) ──── */
        otpForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Collect OTP from all boxes
            var otpValue = '';
            otpBoxes.forEach(function (box) {
                otpValue += box.value;
            });

            if (otpValue.length !== 6) {
                authmeToast('error', 'Please enter the complete 6-digit OTP.');
                return;
            }

            var email    = document.getElementById('authme-otp-email').value;
            var purpose  = document.getElementById('authme-otp-purpose').value;
            var userData = document.getElementById('authme-otp-user-data').value;
            var userId   = document.getElementById('authme-otp-user-id').value;
            var remember = document.getElementById('authme-otp-remember').value;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying…';

            // Step 1: Verify OTP
            authmeAjax('authme_verify_otp', {
                email:    email,
                otp_code: otpValue,
                purpose:  purpose,
            },
            function (data) {
                authmeToast('success', data.message);

                if (purpose === 'registration') {
                    // Step 2a: Complete registration
                    var regUserData = data.user_data || userData;
                    authmeAjax('authme_register_user', {
                        user_data: regUserData,
                    },
                    function (regData) {
                        authmeToast('success', regData.message);
                        // Redirect after short delay
                        setTimeout(function () {
                            window.location.href = regData.redirect || window.location.href;
                        }, 1000);
                    },
                    function (regData) {
                        authmeToast('error', regData.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Verify & Proceed';
                    });

                } else if (purpose === 'login') {
                    // Step 2b: Complete login (set auth cookie server-side)
                    authmeAjax('authme_complete_login', {
                        user_id:  userId,
                        remember: remember,
                    },
                    function (loginData) {
                        authmeToast('success', loginData.message || 'Login successful! Redirecting…');
                        setTimeout(function () {
                            window.location.href = loginData.redirect || window.location.href;
                        }, 1000);
                    },
                    function (loginData) {
                        // If the complete_login endpoint doesn't exist yet,
                        // fall back to a page reload (auth cookie may already be set)
                        authmeToast('success', 'Login verified! Redirecting…');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    });
                }
            },
            function (data) {
                authmeToast('error', data.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Verify & Proceed';
            });
        });

        /* ── Resend OTP ──────────────────────── */
        if (resendBtn) {
            resendBtn.addEventListener('click', function () {
                if (timeLeft > 0) return; // Timer still running

                var email   = document.getElementById('authme-otp-email').value;
                var purpose = document.getElementById('authme-otp-purpose').value;
                var userData = document.getElementById('authme-otp-user-data').value;

                var sendData = {
                    email:   email,
                    purpose: purpose,
                };

                // For registration, re-send user_data as well
                if (purpose === 'registration' && userData) {
                    sendData.user_data = userData;
                }

                authmeAjax('authme_send_otp', sendData,
                    function (data) {
                        authmeToast('success', 'A new OTP has been sent to your email.');
                        // Clear OTP boxes
                        otpBoxes.forEach(function (box) {
                            box.value = '';
                            box.classList.remove('authme-otp-filled');
                        });
                        otpBoxes[0].focus();
                        // Restart timer
                        authmeStartOtpTimer();
                    },
                    function (data) {
                        authmeToast('error', data.message);
                    }
                );
            });
        }

    });

    /* ── Timer Functions ─────────────────── */

    function authmeStartOtpTimer() {
        timeLeft = OTP_DURATION;
        var resendBtn = document.getElementById('authme-resend-btn');
        if (!resendBtn) return;

        // Set initial state
        resendBtn.classList.add('authme-link-disabled');
        resendBtn.innerHTML = 'Resend in <b id="authme-otp-timer">' + timeLeft + '</b>s';

        // Clear any existing timer
        if (timerId) clearInterval(timerId);

        timerId = setInterval(function () {
            timeLeft--;
            var timerEl = document.getElementById('authme-otp-timer');
            if (timerEl) {
                timerEl.textContent = timeLeft;
            }

            if (timeLeft <= 0) {
                clearInterval(timerId);
                timerId = null;
                resendBtn.classList.remove('authme-link-disabled');
                resendBtn.innerHTML = 'Resend OTP';
            }
        }, 1000);
    }

    // Expose timer start globally (called by login.js / register.js)
    window.authmeStartOtpTimer = authmeStartOtpTimer;

})(window);
