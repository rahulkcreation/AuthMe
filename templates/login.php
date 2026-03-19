<?php
/**
 * AuthMe Login Screen Template
 *
 * Rendered inside the overlay container.
 * Fields: Email/Username, Password (with eye toggle), Remember Me.
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="authme-login-screen" class="authme-screen authme-screen-active">

    <h2 class="authme-form-title">Login Now</h2>
    <p class="authme-form-subtitle">Enter your details to access your account.</p>

    <form id="authme-login-form" class="authme-form" autocomplete="off" novalidate>

        <!-- Email / Username Field -->
        <div class="authme-input-group">
            <label for="authme-login-identifier">Email / Username</label>
            <input type="text" id="authme-login-identifier" class="authme-input"
                   placeholder="name@example.com or username" required>
            <span id="authme-login-identifier-msg" class="authme-field-msg"></span>
        </div>

        <!-- Password Field -->
        <div class="authme-input-group">
            <label for="authme-login-password">Password</label>
            <div class="authme-password-wrapper">
                <input type="password" id="authme-login-password" class="authme-input"
                       placeholder="••••••••" required>
                <button type="button" class="authme-toggle-password" data-target="authme-login-password" aria-label="Toggle password visibility">
                    <!-- Eye-off icon (default) -->
                    <svg class="authme-eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    <!-- Eye-on icon (hidden) -->
                    <svg class="authme-eye-on" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>
            <span id="authme-login-password-msg" class="authme-field-msg"></span>
        </div>

        <!-- Remember Me -->
        <div class="authme-remember-me">
            <input type="checkbox" id="authme-login-remember">
            <label for="authme-login-remember">Remember me</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="authme-login-submit-btn" class="authme-btn authme-btn-primary" disabled>Send OTP</button>

    </form>

    <!-- Switch to Register -->
    <p class="authme-switch-link">
        New here?
        <span class="authme-link" data-screen="authme-register-screen">Create an account</span>
    </p>

</div>
