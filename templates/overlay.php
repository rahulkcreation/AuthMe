<?php
/**
 * AuthMe Overlay Container
 *
 * Main overlay wrapper that houses the login, register,
 * and OTP screens. Rendered hidden and opened via JS.
 * Uses position:fixed + z-index:1000 for modal popup.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- AuthMe Overlay Backdrop + Container -->
<div id="authme-overlay-backdrop" class="authme-overlay-backdrop" style="display:none;">

    <div id="authme-overlay-container" class="authme-overlay-container">

        <!-- Close Button -->
        <button type="button" id="authme-overlay-close" class="authme-overlay-close" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <!-- Login Screen -->
        <?php include AUTHME_PLUGIN_DIR . 'templates/login.php'; ?>

        <!-- Register Screen -->
        <?php include AUTHME_PLUGIN_DIR . 'templates/register.php'; ?>

        <!-- OTP Verification Screen (shared for login, registration, password reset) -->
        <?php include AUTHME_PLUGIN_DIR . 'templates/otp.php'; ?>

        <!-- Forgot Password Screen -->
        <?php include AUTHME_PLUGIN_DIR . 'templates/forgot-password.php'; ?>

        <!-- New Password Screen (shown after OTP verified on reset flow) -->
        <?php include AUTHME_PLUGIN_DIR . 'templates/new-password.php'; ?>

    </div>

</div>

