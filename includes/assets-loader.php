<?php
/**
 * AuthMe — Centralized Assets Loader
 *
 * This file is the SINGLE source of truth for all file paths
 * (CSS, JS, templates, includes) used across the plugin.
 * Every other file references paths from here.
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Assets_Loader {

    /* ================================================
     * 📁 PATH REGISTRY
     * ================================================
     * All plugin file paths are defined here.
     * Use AuthMe_Assets_Loader::get() to access them.
     * ================================================ */

    /**
     * Get all registered file paths.
     *
     * Each entry contains:
     *   'dir'  → Absolute server path (for file_exists / filemtime)
     *   'url'  → Public URL (for wp_enqueue)
     *
     * @return array Associative array of all file paths.
     */
    public static function get_paths() {

        $base_dir = AUTHME_PLUGIN_DIR;
        $base_url = AUTHME_PLUGIN_URL;

        return array(

            /* ── CSS Files ───────────────────── */
            'css_global'    => array(
                'dir' => $base_dir . 'assets/css/global.css',
                'url' => $base_url . 'assets/css/global.css',
            ),
            'css_overlay'   => array(
                'dir' => $base_dir . 'assets/css/overlay.css',
                'url' => $base_url . 'assets/css/overlay.css',
            ),
            'css_login'     => array(
                'dir' => $base_dir . 'assets/css/login.css',
                'url' => $base_url . 'assets/css/login.css',
            ),
            'css_register'  => array(
                'dir' => $base_dir . 'assets/css/register.css',
                'url' => $base_url . 'assets/css/register.css',
            ),
            'css_forgot_password' => array(
                'dir' => $base_dir . 'assets/css/forgot-password.css',
                'url' => $base_url . 'assets/css/forgot-password.css',
            ),
            'css_new_password'    => array(
                'dir' => $base_dir . 'assets/css/new-password.css',
                'url' => $base_url . 'assets/css/new-password.css',
            ),
            'css_otp'       => array(
                'dir' => $base_dir . 'assets/css/otp.css',
                'url' => $base_url . 'assets/css/otp.css',
            ),
            'css_toaster'   => array(
                'dir' => $base_dir . 'assets/css/toaster.css',
                'url' => $base_url . 'assets/css/toaster.css',
            ),

            /* ── JS Files ────────────────────── */
            'js_global'     => array(
                'dir' => $base_dir . 'assets/js/global.js',
                'url' => $base_url . 'assets/js/global.js',
            ),
            'js_toaster'    => array(
                'dir' => $base_dir . 'assets/js/toaster.js',
                'url' => $base_url . 'assets/js/toaster.js',
            ),
            'js_overlay'    => array(
                'dir' => $base_dir . 'assets/js/overlay.js',
                'url' => $base_url . 'assets/js/overlay.js',
            ),
            'js_login'      => array(
                'dir' => $base_dir . 'assets/js/login.js',
                'url' => $base_url . 'assets/js/login.js',
            ),
            'js_register'   => array(
                'dir' => $base_dir . 'assets/js/register.js',
                'url' => $base_url . 'assets/js/register.js',
            ),
            'js_forgot_password' => array(
                'dir' => $base_dir . 'assets/js/forgot-password.js',
                'url' => $base_url . 'assets/js/forgot-password.js',
            ),
            'js_new_password'    => array(
                'dir' => $base_dir . 'assets/js/new-password.js',
                'url' => $base_url . 'assets/js/new-password.js',
            ),
            'js_otp'        => array(
                'dir' => $base_dir . 'assets/js/otp.js',
                'url' => $base_url . 'assets/js/otp.js',
            ),

            /* ── Template Files ──────────────── */
            'tpl_overlay'   => array(
                'dir' => $base_dir . 'templates/overlay.php',
            ),
            'tpl_login'     => array(
                'dir' => $base_dir . 'templates/login.php',
            ),
            'tpl_register'  => array(
                'dir' => $base_dir . 'templates/register.php',
            ),
            'tpl_otp'       => array(
                'dir' => $base_dir . 'templates/otp.php',
            ),
            'tpl_toaster'   => array(
                'dir' => $base_dir . 'templates/toaster.php',
            ),
            'tpl_forgot_password' => array(
                'dir' => $base_dir . 'templates/forgot-password.php',
            ),
            'tpl_new_password'    => array(
                'dir' => $base_dir . 'templates/new-password.php',
            ),
            'tpl_email_password_changed' => array(
                'dir' => $base_dir . 'templates/email-password-changed.php',
            ),
            'tpl_email_otp' => array(
                'dir' => $base_dir . 'templates/email-otp.php',
            ),

            /* ── Admin Files ─────────────────── */
            'admin_css'     => array(
                'dir' => $base_dir . 'admin/assets/admin.css',
                'url' => $base_url . 'admin/assets/admin.css',
            ),
            'admin_js'      => array(
                'dir' => $base_dir . 'admin/assets/admin.js',
                'url' => $base_url . 'admin/assets/admin.js',
            ),
            'admin_dashboard' => array(
                'dir' => $base_dir . 'admin/templates/dashboard.php',
            ),
            'admin_database'  => array(
                'dir' => $base_dir . 'admin/templates/database.php',
            ),

            /* ── Include Files ───────────────── */
            'inc_db'        => array(
                'dir' => $base_dir . 'includes/class-authme-db.php',
            ),
            'inc_auth'      => array(
                'dir' => $base_dir . 'includes/class-authme-auth.php',
            ),
            'inc_otp'       => array(
                'dir' => $base_dir . 'includes/class-authme-otp.php',
            ),
            'inc_email'     => array(
                'dir' => $base_dir . 'includes/class-authme-email.php',
            ),
            'inc_assets'    => array(
                'dir' => $base_dir . 'includes/assets-loader.php',
            ),
        );
    }

    /* ================================================
     * 🔍 QUICK ACCESSORS
     * ================================================ */

    /**
     * Get the server path (dir) for a specific file key.
     *
     * @param string $key File key from the path registry.
     * @return string|null Absolute file path, or null if not found.
     */
    public static function dir( $key ) {
        $paths = self::get_paths();
        return isset( $paths[ $key ]['dir'] ) ? $paths[ $key ]['dir'] : null;
    }

    /**
     * Get the public URL for a specific file key.
     *
     * @param string $key File key from the path registry.
     * @return string|null Public URL, or null if not found.
     */
    public static function url( $key ) {
        $paths = self::get_paths();
        return isset( $paths[ $key ]['url'] ) ? $paths[ $key ]['url'] : null;
    }

    /**
     * Get the filemtime version for cache-busting.
     *
     * @param string $key File key from the path registry.
     * @return int|false File modification time, or false on failure.
     */
    public static function version( $key ) {
        $dir = self::dir( $key );
        return ( $dir && file_exists( $dir ) ) ? filemtime( $dir ) : AUTHME_VERSION;
    }

    /* ================================================
     * 🎨 ENQUEUE FRONTEND ASSETS
     * ================================================ */

    /**
     * Enqueue all frontend CSS and JS files.
     * Hooked to 'wp_enqueue_scripts'.
     */
    public static function enqueue_frontend() {

        /* ── CSS Files ───────────────────── */
        $css_files = array( 'global', 'overlay', 'login', 'register', 'otp', 'toaster', 'forgot_password', 'new_password' );

        foreach ( $css_files as $name ) {
            $key = 'css_' . $name;
            $dir = self::dir( $key );
            $url = self::url( $key );

            if ( $dir && $url && file_exists( $dir ) ) {
                wp_enqueue_style(
                    'authme-' . $name,
                    $url,
                    array(),
                    self::version( $key )
                );
            }
        }

        /* ── JS Files with Dependencies ──── */
        $js_files = array(
            'global'   => array(),
            'toaster'  => array( 'authme-global' ),
            'overlay'  => array( 'authme-global' ),
            'login'    => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'register' => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'otp'             => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'forgot_password' => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
            'new_password'    => array( 'authme-global', 'authme-toaster', 'authme-overlay' ),
        );

        foreach ( $js_files as $name => $deps ) {
            $key = 'js_' . $name;
            $dir = self::dir( $key );
            $url = self::url( $key );

            if ( $dir && $url && file_exists( $dir ) ) {
                wp_enqueue_script(
                    'authme-' . $name,
                    $url,
                    $deps,
                    self::version( $key ),
                    true // Load in footer
                );
            }
        }

        /* ── Localize AJAX data ──────────── */
        wp_localize_script( 'authme-global', 'authme_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'authme_nonce' ),
        ) );
    }
}
