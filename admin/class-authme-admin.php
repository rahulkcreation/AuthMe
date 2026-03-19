<?php
/**
 * AuthMe Admin Panel
 *
 * Registers admin menu pages, enqueues admin assets,
 * and provides AJAX handlers for database management.
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Admin AJAX endpoints
        add_action( 'wp_ajax_authme_admin_check_db', array( $this, 'ajax_check_db_status' ) );
        add_action( 'wp_ajax_authme_admin_create_tables', array( $this, 'ajax_create_tables' ) );
    }

    /* ──────────────────────────────────────── */

    /**
     * Register admin menu and sub-menu pages.
     */
    public function register_admin_menus() {
        // Main menu: AuthMe Dashboard
        add_menu_page(
            'AuthMe',                          // Page title
            'AuthMe',                          // Menu title
            'manage_options',                   // Capability
            'authme',                           // Menu slug
            array( $this, 'render_dashboard' ), // Callback
            'dashicons-lock',                   // Icon
            80                                  // Position
        );

        // Sub-menu: Database
        add_submenu_page(
            'authme',                           // Parent slug
            'AuthMe Database',                  // Page title
            'Database',                         // Menu title
            'manage_options',                   // Capability
            'authme-database',                  // Menu slug
            array( $this, 'render_database' )   // Callback
        );
    }

    /* ──────────────────────────────────────── */

    /**
     * Enqueue admin CSS and JS only on AuthMe admin pages.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on AuthMe pages
        if ( strpos( $hook, 'authme' ) === false ) {
            return;
        }

        $css_file = AUTHME_PLUGIN_DIR . 'admin/assets/admin.css';
        $js_file  = AUTHME_PLUGIN_DIR . 'admin/assets/admin.js';

        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'authme-admin-css',
                AUTHME_PLUGIN_URL . 'admin/assets/admin.css',
                array(),
                filemtime( $css_file )
            );
        }

        if ( file_exists( $js_file ) ) {
            wp_enqueue_script(
                'authme-admin-js',
                AUTHME_PLUGIN_URL . 'admin/assets/admin.js',
                array( 'jquery' ),
                filemtime( $js_file ),
                true
            );

            wp_localize_script( 'authme-admin-js', 'authme_admin', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'authme_admin_nonce' ),
            ) );
        }
    }

    /* ──────────────────────────────────────── */

    /**
     * Render the Dashboard page.
     */
    public function render_dashboard() {
        include AUTHME_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }

    /**
     * Render the Database management page.
     */
    public function render_database() {
        include AUTHME_PLUGIN_DIR . 'admin/templates/database.php';
    }

    /* ──────────────────────────────────────── */

    /**
     * AJAX: Check database table status.
     */
    public function ajax_check_db_status() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $db     = new AuthMe_DB();
        $status = $db->check_table_status();

        wp_send_json_success( $status );
    }

    /**
     * AJAX: Create or update database tables.
     */
    public function ajax_create_tables() {
        check_ajax_referer( 'authme_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
        }

        $db = new AuthMe_DB();

        // Check current status before creating
        $status_before = $db->check_table_status();

        if ( $status_before['all_good'] ) {
            wp_send_json_success( array(
                'message' => 'All tables already exist with the correct structure.',
                'status'  => 'already_created',
            ) );
        }

        // Create / update tables
        $db->create_tables();

        // Verify after creation
        $status_after = $db->check_table_status();

        if ( $status_after['all_good'] ) {
            $msg = $status_before['table_exists']
                ? 'Table updated successfully.'
                : 'Table created successfully.';

            wp_send_json_success( array(
                'message' => $msg,
                'status'  => 'created',
            ) );
        }

        wp_send_json_error( array( 'message' => 'Failed to create tables. Please try again.' ) );
    }
}

// Initialize admin
new AuthMe_Admin();
