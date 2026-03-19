<?php
/**
 * AuthMe Email Handler
 *
 * Sends beautifully designed OTP emails using WordPress wp_mail().
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Email {

    /**
     * Send an OTP email to the user.
     *
     * @param string $to_email  Recipient email address.
     * @param string $otp_code  The 6-digit OTP code.
     * @param string $purpose   Either 'registration' or 'login'.
     * @return bool             True if mail was sent successfully.
     */
    public function send_otp_email( $to_email, $otp_code, $purpose = 'registration' ) {

        $subject = ( $purpose === 'login' )
            ? 'Your Login Verification Code — AuthMe'
            : 'Your Registration Verification Code — AuthMe';

        // Build the HTML email body from the template
        $body = $this->get_email_template( $otp_code, $purpose );

        // Set content type to HTML
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        $sent = wp_mail( $to_email, $subject, $body, $headers );

        return $sent;
    }

    /* ──────────────────────────────────────── */

    /**
     * Get the HTML email template with the OTP code injected.
     *
     * @param string $otp_code  The 6-digit OTP.
     * @param string $purpose   registration | login.
     * @return string           HTML email body.
     */
    private function get_email_template( $otp_code, $purpose ) {
        ob_start();
        // Variables available inside the template
        $authme_otp_code = $otp_code;
        $authme_otp_purpose = $purpose;
        include AUTHME_PLUGIN_DIR . 'templates/email-otp.php';
        return ob_get_clean();
    }
}
