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
     * AJAX: Validate login credentials (before OTP)
     * ────────────────────────────────────────────────── */
    public function ajax_login_user() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( $_POST['identifier'] ) : '';
        $password   = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $remember   = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';

        if ( empty( $identifier ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'All fields are required.' ) );
        }

        // Determine user
        if ( strpos( $identifier, '@' ) !== false ) {
            $user = get_user_by( 'email', sanitize_email( $identifier ) );
        } else {
            $user = get_user_by( 'login', $identifier );
        }

        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found. Please check your credentials.' ) );
        }

        // Verify password
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( array( 'message' => 'Incorrect password. Please try again.' ) );
        }

        // Credentials valid — frontend will now trigger OTP send
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
     * AJAX: Complete login (called after OTP verification)
     * ────────────────────────────────────────────────── */
    public function ajax_complete_login() {
        check_ajax_referer( 'authme_nonce', 'nonce' );

        $user_id  = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $remember = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';

        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
        }

        $user = get_user_by( 'ID', $user_id );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'User not found.' ) );
        }

        // Set the auth cookie and log the user in
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, $remember );

        wp_send_json_success( array(
            'message'  => 'Login successful! Welcome back.',
        ) );
    }
}
