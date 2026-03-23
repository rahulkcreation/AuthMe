/**
 * AuthMe — New Password Screen Controller
 *
 * Handles the new-password step of the forgot password flow.
 * Runs after OTP is verified (purpose = 'password_reset').
 *
 * Features:
 *   - Password strength indicator (same logic as register.js)
 *   - Eye-toggle buttons for both fields
 *   - Passwords must match before submit is enabled
 *   - On success: shows toast, sends confirmation email (server-side),
 *     then closes the overlay after 2s
 *
 * @package AuthMe
 */

/* global authmeAjax, authmeToast, authmeCloseOverlay, authmeSetFieldState */

(function (window) {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {

        var form            = document.getElementById("authme-new-password-form");
        var emailInput      = document.getElementById("authme-new-password-email");
        var newPassInput    = document.getElementById("authme-new-password");
        var newPassMsg      = document.getElementById("authme-new-password-msg");
        var confirmInput    = document.getElementById("authme-confirm-password");
        var confirmMsg      = document.getElementById("authme-confirm-password-msg");
        var strengthBar     = document.getElementById("authme-new-password-strength");
        var submitBtn       = document.getElementById("authme-new-password-submit-btn");

        if (!form || !newPassInput) return;

        /* ── Password strength checker ────────── */
        function getStrength(pass) {
            var score = 0;
            if (pass.length >= 8)                         score++;
            if (/[A-Z]/.test(pass))                       score++;
            if (/[0-9]/.test(pass))                       score++;
            if (/[^A-Za-z0-9]/.test(pass))               score++;
            return score; // 0-4
        }

        function renderStrength(pass) {
            if (!strengthBar) return;
            if (!pass) {
                strengthBar.innerHTML = "";
                strengthBar.className = "";
                return;
            }
            var score = getStrength(pass);
            var labels = ["", "strength-weak", "strength-fair", "strength-good", "strength-strong"];
            strengthBar.className = labels[score] || "strength-weak";
            strengthBar.innerHTML =
                '<span class="authme-strength-bar"></span>' +
                '<span class="authme-strength-bar"></span>' +
                '<span class="authme-strength-bar"></span>' +
                '<span class="authme-strength-bar"></span>';
        }

        /* ── Validate & toggle submit button ──── */
        function validateForm() {
            var pass    = newPassInput.value;
            var confirm = confirmInput.value;
            var strong  = getStrength(pass) >= 3; // Require at least "good"
            var matches = pass === confirm && confirm.length > 0;
            submitBtn.disabled = !(pass.length >= 8 && strong && matches);
        }

        /* ── New password input events ────────── */
        newPassInput.addEventListener("input", function () {
            var pass = newPassInput.value;
            renderStrength(pass);

            // Validate strength
            if (pass.length === 0) {
                authmeSetFieldState(newPassInput, newPassMsg, "", "");
            } else if (pass.length < 8) {
                authmeSetFieldState(newPassInput, newPassMsg, "error", "Password must be at least 8 characters.");
            } else if (getStrength(pass) < 3) {
                authmeSetFieldState(newPassInput, newPassMsg, "error", "Password is too weak. Add uppercase, numbers, or symbols.");
            } else {
                authmeSetFieldState(newPassInput, newPassMsg, "success", "Strong password!");
            }

            // Re-validate confirm field if already typed
            if (confirmInput.value.length > 0) {
                if (newPassInput.value !== confirmInput.value) {
                    authmeSetFieldState(confirmInput, confirmMsg, "error", "Passwords do not match.");
                } else {
                    authmeSetFieldState(confirmInput, confirmMsg, "success", "Passwords match.");
                }
            }
            validateForm();
        });

        /* ── Confirm password input events ────── */
        confirmInput.addEventListener("input", function () {
            if (confirmInput.value.length === 0) {
                authmeSetFieldState(confirmInput, confirmMsg, "", "");
            } else if (confirmInput.value !== newPassInput.value) {
                authmeSetFieldState(confirmInput, confirmMsg, "error", "Passwords do not match.");
            } else {
                authmeSetFieldState(confirmInput, confirmMsg, "success", "Passwords match.");
            }
            validateForm();
        });

        /* ── Form Submit ────────────────────────── */
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            var email    = emailInput ? emailInput.value : "";
            var password = newPassInput.value;

            if (!email) {
                authmeToast("error", "Session expired. Please start the reset process again.");
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = "Resetting…";

            authmeAjax(
                "authme_reset_password",
                { email: email, new_password: password },
                function (data) {
                    // Close overlay instantly — no visible blank screen
                    if (typeof authmeCloseOverlay === "function") {
                        authmeCloseOverlay();
                    }

                    // Show success toast (floats above the now-closed overlay)
                    authmeToast("success", data.message || "Password reset successfully!");

                    // Reset form state silently (hidden, so no flicker)
                    newPassInput.value  = "";
                    confirmInput.value  = "";
                    if (strengthBar) { strengthBar.innerHTML = ""; strengthBar.className = ""; }
                    authmeSetFieldState(newPassInput, newPassMsg, "", "");
                    authmeSetFieldState(confirmInput, confirmMsg, "", "");
                    submitBtn.disabled    = true;
                    submitBtn.textContent = "Reset Password";
                    if (emailInput) emailInput.value = "";
                },
                function (errData) {
                    authmeToast("error", errData.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Reset Password";
                }
            );
        });

    });

})(window);
