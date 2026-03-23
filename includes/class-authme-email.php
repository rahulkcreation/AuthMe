<?php
/**
 * AuthMe Email Handler
 *
 * Sends beautifully designed OTP emails using WordPress wp_mail().
 *
 * @package AuthMe
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

        if ( $purpose === 'login' ) {
            $subject = 'Your Login Verification Code — AuthMe';
        } elseif ( $purpose === 'password_reset' ) {
            $subject = 'Your Reset Password Verification Code — AuthMe';
        } else {
            $subject = 'Your Registration Verification Code — AuthMe';
        }

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
    /* ──────────────────────────────────────── */

    /**
     * Send a "password changed" notification email.
     *
     * @param string $to_email    Recipient email address.
     * @param string $user_name   User's display name.
     * @return bool               True if mail was sent successfully.
     */
    public function send_password_changed_email( $to_email, $user_name ) {

        $subject = 'Your Password Was Changed — ' . get_bloginfo( 'name' );

        $body = $this->get_password_changed_template( $user_name );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail( $to_email, $subject, $body, $headers );
    }

    /* ──────────────────────────────────────── */

    /**
     * Get the HTML template for the password-changed email.
     *
     * @param string $user_name  User's display name.
     * @return string            HTML email body.
     */
    private function get_password_changed_template( $user_name ) {
        ob_start();
        $authme_user_name = $user_name;
        include AUTHME_PLUGIN_DIR . 'templates/email-password-changed.php';
        return ob_get_clean();
    }
}

