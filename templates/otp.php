<?php
/**
 * AuthMe OTP Verification Screen Template
 *
 * Rendered inside the overlay container after the user
 * clicks "Send OTP" on login or register.
 * 6 individual digit input boxes with auto-tab.
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="authme-otp-screen" class="authme-screen">

    <h2 class="authme-form-title">Verification</h2>
    <p class="authme-form-subtitle">We've sent a 6-digit code to your email.</p>

    <form id="authme-otp-form" class="authme-form" novalidate>

        <!-- 6-Digit OTP Boxes -->
        <div class="authme-otp-container" id="authme-otp-container">
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="authme-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        </div>

        <!-- Verify Button -->
        <button type="submit" id="authme-otp-submit-btn" class="authme-btn authme-btn-primary">Verify & Proceed</button>

    </form>

    <!-- Resend OTP -->
    <p class="authme-switch-link">
        Didn't receive the code?
        <span id="authme-resend-btn" class="authme-link authme-link-disabled">Resend in <b id="authme-otp-timer">60</b>s</span>
    </p>

    <!-- Hidden fields to track OTP context -->
    <input type="hidden" id="authme-otp-email" value="">
    <input type="hidden" id="authme-otp-purpose" value="">
    <input type="hidden" id="authme-otp-user-data" value="">
    <input type="hidden" id="authme-otp-user-id" value="">
    <input type="hidden" id="authme-otp-remember" value="">

</div>
