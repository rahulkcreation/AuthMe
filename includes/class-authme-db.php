<?php
/**
 * AuthMe Database Manager
 *
 * Handles creation, verification, and management of the
 * wp_authme_otp_storage custom database table.
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AuthMe_DB {

    /**
     * Full table name including WP prefix.
     *
     * @var string
     */
    private $table_name;

    /**
     * Required columns in the OTP storage table.
     *
     * @var array
     */
    private $required_columns = array(
        'id', 'email', 'otp_code', 'purpose',
        'created_at', 'expires_at', 'is_verified', 'user_data',
    );

    /* ──────────────────────────────────────── */

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'authme_otp_storage';
    }

    /* ──────────────────────────────────────── */

    /**
     * Create the OTP storage table using dbDelta.
     *
     * @return bool True on success.
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            purpose VARCHAR(20) NOT NULL DEFAULT 'registration',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_verified TINYINT(1) NOT NULL DEFAULT 0,
            user_data TEXT,
            PRIMARY KEY (id),
            KEY email_purpose (email, purpose)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        return true;
    }

    /* ──────────────────────────────────────── */

    /**
     * Check the status of the OTP storage table and its columns.
     *
     * @return array Associative array with table existence and column details.
     */
    public function check_table_status() {
        global $wpdb;

        $status = array(
            'table_exists'    => false,
            'columns'         => array(),
            'missing_columns' => array(),
            'all_good'        => false,
        );

        // Check if the table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare( "SHOW TABLES LIKE %s", $this->table_name )
        );

        if ( $table_exists !== $this->table_name ) {
            $status['missing_columns'] = $this->required_columns;
            return $status;
        }

        $status['table_exists'] = true;

        // Get existing columns
        $existing_columns = $wpdb->get_col( "SHOW COLUMNS FROM {$this->table_name}", 0 );

        foreach ( $this->required_columns as $col ) {
            $exists = in_array( $col, $existing_columns, true );
            $status['columns'][ $col ] = $exists;
            if ( ! $exists ) {
                $status['missing_columns'][] = $col;
            }
        }

        $status['all_good'] = empty( $status['missing_columns'] );

        return $status;
    }

    /* ──────────────────────────────────────── */

    /**
     * Get the table name.
     *
     * @return string
     */
    public function get_table_name() {
        return $this->table_name;
    }
}
