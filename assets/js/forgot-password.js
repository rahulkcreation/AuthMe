/**
 * AuthMe — Forgot Password Screen Controller
 *
 * Handles the forgot password flow:
 *   1. User enters email or username
 *   2. Backend checks if account exists & sends OTP (purpose: 'password_reset')
 *   3. Switches to OTP screen for verification
 *   4. After OTP verified, switches to new-password screen
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeToast, authmeShowScreen, authmeSetFieldState */

(function (window) {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {

        var form        = document.getElementById("authme-forgot-form");
        var identInput  = document.getElementById("authme-forgot-identifier");
        var identMsg    = document.getElementById("authme-forgot-identifier-msg");
        var submitBtn   = document.getElementById("authme-forgot-submit-btn");

        if (!form || !identInput) return;

        /* ── Enable button only when field has value ── */
        identInput.addEventListener("input", function () {
            submitBtn.disabled = identInput.value.trim().length === 0;
            // Reset field state on new input
            if (identMsg) {
                identMsg.textContent = "";
                identMsg.className = "authme-field-msg";
            }
            identInput.classList.remove("authme-input-success", "authme-input-error");
        });

        /* ── Form Submit ────────────────────────── */
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var identifier = identInput.value.trim();
            if (!identifier) return;

            submitBtn.disabled = true;
            submitBtn.textContent = "Checking…";

            // Step 1: Check if account exists with this email/username
            authmeAjax(
                "authme_forgot_check_user",
                { identifier: identifier },
                function (data) {
                    // Account found — send OTP for password_reset
                    var email = data.email;

                    authmeSetFieldState(identInput, identMsg, "success", "Account found. Sending OTP…");

                    authmeAjax(
                        "authme_send_otp",
                        { email: email, purpose: "password_reset" },
                        function () {
                            authmeToast("success", "OTP sent to your email.");

                            // Pass the email to the OTP screen
                            var otpEmailField = document.getElementById("authme-otp-email");
                            var otpPurposeField = document.getElementById("authme-otp-purpose");
                            var otpUserIdField = document.getElementById("authme-otp-user-id");

                            if (otpEmailField)  otpEmailField.value  = email;
                            if (otpPurposeField) otpPurposeField.value = "password_reset";
                            if (otpUserIdField)  otpUserIdField.value  = data.user_id || "";

                            // Start the OTP countdown timer
                            if (typeof window.authmeStartOtpTimer === "function") {
                                window.authmeStartOtpTimer();
                            }

                            authmeShowScreen("authme-otp-screen");

                            // Reset form for next use
                            identInput.value = "";
                            identInput.classList.remove("authme-input-success", "authme-input-error");
                            if (identMsg) { identMsg.textContent = ""; identMsg.className = "authme-field-msg"; }
                            submitBtn.textContent = "Send OTP";
                        },
                        function (errData) {
                            authmeToast("error", errData.message);
                            submitBtn.disabled = false;
                            submitBtn.textContent = "Send OTP";
                        }
                    );
                },
                function (errData) {
                    // Account not found
                    authmeSetFieldState(identInput, identMsg, "error", errData.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Send OTP";
                }
            );
        });

    });

})(window);
