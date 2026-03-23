<?php
/**
 * AuthMe New Password Screen Template
 *
 * Rendered inside the overlay container.
 * Shown after OTP verification on the forgot-password flow.
 * Fields: New password + confirm password, both with eye-toggle.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="authme-new-password-screen" class="authme-screen">

    <!-- Hidden: email passed forward from forgot-password step -->
    <input type="hidden" id="authme-new-password-email" value="">

    <h2 class="authme-form-title">Set New Password</h2>
    <p class="authme-form-subtitle">Choose a strong password for your account.</p>

    <form id="authme-new-password-form" class="authme-form" autocomplete="off" novalidate>

        <!-- New Password Field -->
        <div class="authme-input-group">
            <label for="authme-new-password">New Password</label>
            <div class="authme-password-wrapper">
                <input type="password" id="authme-new-password" class="authme-input"
                       placeholder="••••••••" required>
                <button type="button" class="authme-toggle-password"
                        data-target="authme-new-password" aria-label="Toggle password visibility">
                    <svg class="authme-eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg class="authme-eye-on" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <!-- Password Strength Indicator -->
            <div id="authme-new-password-strength"></div>
            <span id="authme-new-password-msg" class="authme-field-msg"></span>
        </div>

        <!-- Confirm Password Field -->
        <div class="authme-input-group">
            <label for="authme-confirm-password">Confirm New Password</label>
            <div class="authme-password-wrapper">
                <input type="password" id="authme-confirm-password" class="authme-input"
                       placeholder="••••••••" required>
                <button type="button" class="authme-toggle-password"
                        data-target="authme-confirm-password" aria-label="Toggle password visibility">
                    <svg class="authme-eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg class="authme-eye-on" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <span id="authme-confirm-password-msg" class="authme-field-msg"></span>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="authme-new-password-submit-btn" class="authme-btn authme-btn-primary" disabled>
            Reset Password
        </button>

    </form>

</div>
