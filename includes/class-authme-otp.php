<?php
/**
 * AuthMe OTP Manager
 *
 * Handles OTP generation, storage, verification, and resend logic
 * for both registration and login flows.
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_OTP {

    /**
     * OTP validity in seconds.
     */
    const OTP_EXPIRY_SECONDS = 60;

    /**
     * Database table name.
     *
     * @var string
     */
    private $table_name;

    /* ──────────────────────────────────────── */

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'authme_otp_storage';
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Generate and send an OTP.
     *
     * Expected POST params:
     *   - email     (string)  Recipient email.
     *   - purpose   (string)  'registration' or 'login'.
     *   - user_data (string)  JSON user data (only for registration).
     */
    public function ajax_send_otp() {
        // Verify nonce
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $purpose = isset( $_POST['purpose'] ) ? sanitize_text_field( $_POST['purpose'] ) : '';

        if ( empty( $email ) || empty( $purpose ) ) {
            wp_send_json_error( array( 'message' => 'Email and purpose are required.' ) );
        }

        if ( ! in_array( $purpose, array( 'registration', 'login' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid OTP purpose.' ) );
        }

        // Generate a random 6-digit OTP
        $otp_code = str_pad( wp_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );

        // Invalidate any previous OTPs for this email + purpose
        $this->invalidate_previous_otps( $email, $purpose );

        // User data (for registration — temporary storage)
        $user_data = null;
        if ( $purpose === 'registration' && isset( $_POST['user_data'] ) ) {
            $user_data = sanitize_text_field( wp_unslash( $_POST['user_data'] ) );
        }

        // Calculate expiry timestamp
        $created_at = current_time( 'mysql' );
        $expires_at = gmdate( 'Y-m-d H:i:s', strtotime( $created_at ) + self::OTP_EXPIRY_SECONDS );

        // Store the OTP in the database
        global $wpdb;
        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'email'       => $email,
                'otp_code'    => $otp_code,
                'purpose'     => $purpose,
                'created_at'  => $created_at,
                'expires_at'  => $expires_at,
                'is_verified' => 0,
                'user_data'   => $user_data,
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( ! $inserted ) {
            wp_send_json_error( array( 'message' => 'Failed to store OTP. Please try again.' ) );
        }

        // Send the OTP email
        $email_handler = new AuthMe_Email();
        $sent = $email_handler->send_otp_email( $email, $otp_code, $purpose );

        if ( ! $sent ) {
            wp_send_json_error( array( 'message' => 'Failed to send OTP email. Please try again.' ) );
        }

        wp_send_json_success( array(
            'message' => 'OTP has been sent to your email.',
            'expiry'  => self::OTP_EXPIRY_SECONDS,
        ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX handler: Verify an OTP code.
     *
     * Expected POST params:
     *   - email    (string)  The email the OTP was sent to.
     *   - otp_code (string)  The 6-digit OTP entered by the user.
     *   - purpose  (string)  'registration' or 'login'.
     */
    public function ajax_verify_otp() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $otp_code = isset( $_POST['otp_code'] ) ? sanitize_text_field( $_POST['otp_code'] ) : '';
        $purpose  = isset( $_POST['purpose'] ) ? sanitize_text_field( $_POST['purpose'] ) : '';

        if ( empty( $email ) || empty( $otp_code ) || empty( $purpose ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        global $wpdb;

        // Find the latest unverified OTP for this email + purpose
        $otp_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name}
                 WHERE email = %s AND purpose = %s AND is_verified = 0
                 ORDER BY created_at DESC LIMIT 1",
                $email,
                $purpose
            )
        );

        if ( ! $otp_row ) {
            wp_send_json_error( array( 'message' => 'No OTP found. Please request a new one.' ) );
        }

        // Check expiry
        $now     = strtotime( current_time( 'mysql' ) );
        $expires = strtotime( $otp_row->expires_at );

        if ( $now > $expires ) {
            wp_send_json_error( array( 'message' => 'OTP has expired. Please request a new one.' ) );
        }

        // Check if the code matches
        if ( $otp_row->otp_code !== $otp_code ) {
            wp_send_json_error( array( 'message' => 'Invalid OTP. Please try again.' ) );
        }

        // Mark OTP as verified
        $wpdb->update(
            $this->table_name,
            array( 'is_verified' => 1 ),
            array( 'id' => $otp_row->id ),
            array( '%d' ),
            array( '%d' )
        );

        // Build the response based on purpose
        $response = array( 'message' => 'OTP verified successfully.' );

        // For registration — include user_data so the auth handler can create the user
        if ( $purpose === 'registration' && ! empty( $otp_row->user_data ) ) {
            $response['user_data'] = $otp_row->user_data;
        }

        wp_send_json_success( $response );
    }

    /* ──────────────────────────────────────── */

    /**
     * Invalidate (delete) all previous unverified OTPs
     * for a given email + purpose combination.
     *
     * @param string $email   User email.
     * @param string $purpose OTP purpose.
     */
    private function invalidate_previous_otps( $email, $purpose ) {
        global $wpdb;
        $wpdb->delete(
            $this->table_name,
            array(
                'email'       => $email,
                'purpose'     => $purpose,
                'is_verified' => 0,
            ),
            array( '%s', '%s', '%d' )
        );
    }
}
