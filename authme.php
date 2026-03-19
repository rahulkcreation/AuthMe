<?php

/**
 * Plugin Name: AuthMe
 * Plugin URI: https://arttechfuzion.com
 * Description: A comprehensive WordPress authentication plugin with OTP verification for secure registration and login.
 * Version: 1.4.0
 * Author: Art-Tech Fuzion
 * Author URI: https://arttechfuzion.com
 * Text Domain: authme
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
 * Constants
 * ────────────────────────────────────────────── */
define('AUTHME_VERSION', '1.3.0');
define('AUTHME_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AUTHME_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTHME_PLUGIN_BASENAME', plugin_basename(__FILE__));

/* ──────────────────────────────────────────────
 * Include Files
 * ────────────────────────────────────────────── */
require_once AUTHME_PLUGIN_DIR . 'includes/assets-loader.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-db.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-email.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-otp.php';
require_once AUTHME_PLUGIN_DIR . 'includes/class-authme-auth.php';

// Admin files (only on admin pages)
if (is_admin()) {
    require_once AUTHME_PLUGIN_DIR . 'admin/class-authme-admin.php';
}

/* ──────────────────────────────────────────────
 * Plugin Activation
 * ────────────────────────────────────────────── */
register_activation_hook(__FILE__, 'authme_activate_plugin');

function authme_activate_plugin()
{
    // Create database tables
    $db = new AuthMe_DB();
    $db->create_tables();

    // Register the 'customer' role if it doesn't exist
    if (! get_role('customer')) {
        add_role('customer', 'Customer', array(
            'read' => true,
        ));
    }

    // Register rewrite rules and flush so /authme URL works immediately
    authme_register_rewrite_rules();
    flush_rewrite_rules();

    // Schedule OTP cleanup cron (runs twice daily)
    if (! wp_next_scheduled('authme_otp_cleanup')) {
        wp_schedule_event(time(), 'twicedaily', 'authme_otp_cleanup');
    }
}

/* ──────────────────────────────────────────────
 * Plugin Deactivation
 * ────────────────────────────────────────────── */
register_deactivation_hook(__FILE__, 'authme_deactivate_plugin');

function authme_deactivate_plugin()
{
    flush_rewrite_rules();

    // Unschedule OTP cleanup cron
    $timestamp = wp_next_scheduled('authme_otp_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'authme_otp_cleanup');
    }
}

/* ──────────────────────────────────────────────
 * OTP Auto-Cleanup Cron Hook
 *
 * Runs twice daily to delete expired/verified OTPs
 * from the wp_authme_otp_storage table.
 * Scheduled on activation, unscheduled on deactivation.
 * ────────────────────────────────────────────── */
add_action('authme_otp_cleanup', 'authme_run_otp_cleanup');

function authme_run_otp_cleanup()
{
    $otp = new AuthMe_OTP();
    $otp->cleanup_expired_otps();
}

/* ──────────────────────────────────────────────
 * Enqueue Frontend Assets
 *
 * All file paths are centralized in:
 * includes/assets-loader.php → AuthMe_Assets_Loader
 * ────────────────────────────────────────────── */
add_action('wp_enqueue_scripts', array('AuthMe_Assets_Loader', 'enqueue_frontend'));

/* ──────────────────────────────────────────────
 * Auto-inject Overlay via wp_footer
 *
 * The overlay HTML is injected into every frontend page
 * at the bottom of <body>. It starts hidden (display:none)
 * and is opened via JS when needed.
 *
 * If the URL has ?authme_open=1 (from /authme redirect),
 * the overlay auto-opens on page load.
 * ────────────────────────────────────────────── */
add_action('wp_footer', 'authme_inject_overlay_in_footer');

function authme_inject_overlay_in_footer()
{
    // Don't inject for logged-in users
    if (is_user_logged_in()) {
        return;
    }

    // Include the toaster + overlay templates
    include AUTHME_PLUGIN_DIR . 'templates/toaster.php';
    include AUTHME_PLUGIN_DIR . 'templates/overlay.php';

    // Auto-open if ?authme_open=1 is in the URL (from /authme redirect)
    if (isset($_GET['authme_open']) && $_GET['authme_open'] === '1') {
        echo '<script>document.addEventListener("DOMContentLoaded",function(){if(typeof authmeOpenOverlay==="function"){authmeOpenOverlay();}});</script>';
    }
}

/* ──────────────────────────────────────────────
 * Register AJAX Endpoints
 * ────────────────────────────────────────────── */
$authme_auth = new AuthMe_Auth();
$authme_otp  = new AuthMe_OTP();

// Public (nopriv) + logged-in AJAX actions
$ajax_actions = array(
    'authme_check_username'    => array($authme_auth, 'ajax_check_username'),
    'authme_check_email'       => array($authme_auth, 'ajax_check_email'),
    'authme_check_user_exists' => array($authme_auth, 'ajax_check_user_exists'),
    'authme_login_user'        => array($authme_auth, 'ajax_login_user'),
    'authme_register_user'     => array($authme_auth, 'ajax_register_user'),
    'authme_complete_login'    => array($authme_auth, 'ajax_complete_login'),
    'authme_send_otp'          => array($authme_otp, 'ajax_send_otp'),
    'authme_verify_otp'        => array($authme_otp, 'ajax_verify_otp'),
);

foreach ($ajax_actions as $action => $callback) {
    add_action('wp_ajax_' . $action, $callback);
    add_action('wp_ajax_nopriv_' . $action, $callback);
}

/* ──────────────────────────────────────────────
 * Settings Link on Plugins Page
 * ────────────────────────────────────────────── */
add_filter('plugin_action_links_' . AUTHME_PLUGIN_BASENAME, 'authme_add_settings_link');

function authme_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=authme') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

/* ──────────────────────────────────────────────
 * Virtual /authme Page Endpoint
 *
 * When a user visits yoursite.com/authme, they are
 * redirected to the homepage with ?authme_open=1.
 * The wp_footer hook detects this parameter and
 * auto-opens the overlay — no blank page.
 * ────────────────────────────────────────────── */

/**
 * Register rewrite rule: /authme → index.php?authme_page=1
 */
function authme_register_rewrite_rules()
{
    add_rewrite_rule('^authme/?$', 'index.php?authme_page=1', 'top');
}
add_action('init', 'authme_register_rewrite_rules');

/**
 * Register 'authme_page' as a recognized query variable.
 */
function authme_register_query_vars($vars)
{
    $vars[] = 'authme_page';
    return $vars;
}
add_filter('query_vars', 'authme_register_query_vars');

/**
 * Handle the /authme virtual page:
 * - If logged in → redirect to homepage (no popup needed)
 * - If not logged in → redirect to homepage with ?authme_open=1
 *   The footer hook auto-opens the popup on the real page.
 */
function authme_handle_virtual_page()
{
    if (! get_query_var('authme_page')) {
        return;
    }

    if (is_user_logged_in()) {
        wp_safe_redirect(home_url());
        exit;
    }

    // Redirect to homepage with auto-open trigger parameter
    wp_safe_redirect(home_url('?authme_open=1'));
    exit;
}
add_action('template_redirect', 'authme_handle_virtual_page');

/* ──────────────────────────────────────────────
 * WooCommerce Integration
 *
 * Handles non-logged-in users on WooCommerce pages:
 *
 *   Cart page → "Proceed to Checkout" is intercepted by JS
 *               in overlay.js. Popup opens on the cart page.
 *
 *   Checkout page → If not logged in, redirect back to cart
 *                   (user must log in first via popup on cart).
 *
 *   My Account → If not logged in, redirect to homepage
 *                 with ?authme_open=1 so the popup opens
 *                 on the homepage (no WooCommerce forms visible).
 *
 * Only runs if WooCommerce is active.
 * ────────────────────────────────────────────── */
add_action('template_redirect', 'authme_woo_intercept_pages');

function authme_woo_intercept_pages()
{
    // Only run if WooCommerce is active
    if (! class_exists('WooCommerce')) {
        return;
    }

    // Skip if user is already logged in
    if (is_user_logged_in()) {
        return;
    }

    // Checkout page → redirect back to cart (must log in first)
    if (function_exists('is_checkout') && is_checkout()) {
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url();
        wp_safe_redirect($cart_url);
        exit;
    }

    // My Account page → redirect to homepage with popup
    if (function_exists('is_account_page') && is_account_page()) {
        wp_safe_redirect(home_url('?authme_open=1'));
        exit;
    }
}
