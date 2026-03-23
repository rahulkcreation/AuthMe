<?php
/**
 * AuthMe Authentication Handler
 *
 * Handles user registration, login, and real-time field validation
 * via AJAX endpoints.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Auth {

    /* ──────────────────────────────────────────────────
     * AJAX: Check username availability (Registration)
     * ────────────────────────────────────────────────── */
    public function ajax_check_username() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';

        if ( empty( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username is required.' ) );
        }

        // Must start with an alphabetic character
        if ( ! preg_match( '/^[a-zA-Z]/', $username ) ) {
            wp_send_json_error( array( 'message' => 'Username must start with an alphabet character.' ) );
        }

        // Length: 3–20 characters, alphanumeric only
        if ( ! preg_match( '/^[a-zA-Z][a-zA-Z0-9]{2,19}$/', $username ) ) {
            wp_send_json_error( array( 'message' => 'Username must be 3–20 alphanumeric characters.' ) );
        }

        // Check uniqueness
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username not available.' ) );
        }

        wp_send_json_success( array( 'message' => 'Username available.' ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check email availability (Registration)
     * ────────────────────────────────────────────────── */
    public function ajax_check_email() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email already registered.' ) );
        }

        wp_send_json_success( array( 'message' => 'Email available.' ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check if user exists (Login — user lookup)
     * ────────────────────────────────────────────────── */
    public function ajax_check_user_exists() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';

        if ( empty( $identifier ) ) {
            wp_send_json_error( array( 'message' => 'Please enter your username or email.' ) );
        }

        // Auto-detect: if it contains '@', treat as email
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found. Please check your credentials.' ) );
        }

        wp_send_json_success( array(
            'message' => 'User found.',
            'email'   => $user->user_email,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Login user
     *
     * Validates credentials. If direct_login=true (login form),
     * sets auth cookie immediately and returns success.
     * ────────────────────────────────────────────────── */
    public function ajax_login_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier   = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';
        $password     = isset( $_POST['password'] )   ? $_POST['password'] : '';
        $remember     = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';
        $direct_login = isset( $_POST['direct_login'] ) && $_POST['direct_login'] === 'true';

        if ( empty( $identifier ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        // Determine user by email or username
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'No account found. Please check your credentials.' ) );
        }

        // Verify password
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => 'Incorrect password. Please try again.' ) );
        }

        if ( $direct_login ) {
            // Direct login mode — set auth cookie immediately, no OTP step
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, $remember );
            do_action( 'wp_login', $user->user_login, $user );

            wp_send_json_success( array(
                'message' => 'Login successful! Welcome back, ' . esc_html( $user->display_name ) . '.',
            ) );
        }

        // Fallback: credentials valid — frontend will now trigger OTP send (legacy path)
        wp_send_json_success( array(
            'message'  => 'Credentials verified. Sending OTP…',
            'email'    => $user->user_email,
            'user_id'  => $user->ID,
            'remember' => $remember,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Register user (called after OTP verification)
     * ────────────────────────────────────────────────── */
    public function ajax_register_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        // Expect raw user_data JSON string
        $user_data_raw = isset( $_POST['user_data'] ) ? wp_unslash( $_POST['user_data'] ) : '';

        if ( empty( $user_data_raw ) ) {
            wp_send_json_error( array( 'message' => 'User data is missing.' ) );
        }

        $user_data = json_decode( $user_data_raw, true );

        if ( ! $user_data || empty( $user_data['username'] ) || empty( $user_data['email'] ) || empty( $user_data['password'] ) ) {
            wp_send_json_error( array( 'message' => 'Invalid user data.' ) );
        }

        $username = sanitize_user( $user_data['username'] );
        $email    = sanitize_email( $user_data['email'] );
        $password = $user_data['password'];

        // Double-check uniqueness
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username not available.' ) );
        }
        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email already registered.' ) );
        }

        // Create the user
        $user_id = wp_insert_user( array(
            'user_login'    => $username,
            'user_nicename' => sanitize_title( $username ),
            'user_email'    => $email,
            'user_pass'     => $password,
            'role'          => 'customer',
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        // Auto-login the newly registered user
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        wp_send_json_success( array(
            'message'  => 'Registration successful! Welcome aboard.',
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Check if user exists (Forgot Password — lookup)
     *
     * Accepts email or username. Returns the email address
     * so we can send an OTP to it.
     * ────────────────────────────────────────────────── */
    public function ajax_forgot_check_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';

        if ( empty( $identifier ) ) {
            wp_send_json_error( array( 'message' => 'Please enter your email or username.' ) );
        }

        // Auto-detect: if it contains '@', treat as email; otherwise as username
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array(
                'message' => 'No account found with this detail. Please create one.',
            ) );
        }

        wp_send_json_success( array(
            'email'   => $user->user_email,
            'user_id' => $user->ID,
        ) );
    }

    /* ──────────────────────────────────────────────────
     * AJAX: Reset password (called after OTP verification)
     *
     * Receives the user email and new password.
     * Updates the password in the database and sends
     * a "password changed" email notification.
     * ────────────────────────────────────────────────── */
    public function ajax_reset_password() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $email        = isset( $_POST['email'] )        ? sanitize_email( $_POST['email'] )       : '';
        $new_password = isset( $_POST['new_password'] ) ? wp_unslash( $_POST['new_password'] )    : '';

        if ( empty( $email ) || empty( $new_password ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        // Password strength: minimum 8 chars
        if ( strlen( $new_password ) < 8 ) {
            wp_send_json_error( array( 'message' => 'Password must be at least 8 characters.' ) );
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found.' ) );
        }

        // Update the password
        wp_set_password( $new_password, $user->ID );

        // Send "password changed" email notification
        $email_handler = new AuthMe_Email();
        $email_handler->send_password_changed_email( $email, $user->display_name );

        wp_send_json_success( array(
            'message' => 'Password reset successfully! You can now log in.',
        ) );
    }
}

