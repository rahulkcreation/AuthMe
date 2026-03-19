<?php
/**
 * AuthMe Admin — Database Management Page
 *
 * @package AuthMe
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="authme-admin-wrap" id="authme-admin-database">

    <!-- Page Header -->
    <div class="authme-admin-header">
        <h1 class="authme-admin-title">🗄️ Database Management</h1>
        <p class="authme-admin-subtitle">Check and manage the AuthMe database table.</p>
    </div>

    <!-- Table Status Section -->
    <div class="authme-admin-section">
        <h2>Table Status</h2>
        <div class="authme-admin-table-status" id="authme-db-status-container">
            <p class="authme-admin-loading">Checking database status…</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="authme-admin-section">
        <button type="button" class="authme-admin-btn authme-admin-btn-primary" id="authme-btn-create-tables">
            Create / Update Tables
        </button>
        <button type="button" class="authme-admin-btn authme-admin-btn-secondary" id="authme-btn-refresh-status">
            Refresh Status
        </button>
    </div>

</div>
