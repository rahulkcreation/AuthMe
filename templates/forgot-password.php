<?php
/**
 * AuthMe Forgot Password Screen Template
 *
 * Rendered inside the overlay container.
 * Fields: Email or Username to look up the account.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="authme-forgot-screen" class="authme-screen">

    <h2 class="authme-form-title">Reset Password</h2>
    <p class="authme-form-subtitle">Enter your email or username and we'll send you a verification code.</p>

    <form id="authme-forgot-form" class="authme-form" autocomplete="off" novalidate>

        <!-- Email / Username Field -->
        <div class="authme-input-group">
            <label for="authme-forgot-identifier">Email / Username</label>
            <input type="text" id="authme-forgot-identifier" class="authme-input"
                   placeholder="name@example.com or username" required>
            <span id="authme-forgot-identifier-msg" class="authme-field-msg"></span>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="authme-forgot-submit-btn" class="authme-btn authme-btn-primary" disabled>
            Send OTP
        </button>

    </form>

    <!-- Back to Login -->
    <p class="authme-switch-link">
        Remembered your password?
        <span class="authme-link" data-screen="authme-login-screen">Back to Login</span>
    </p>

</div>
