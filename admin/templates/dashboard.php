<?php
/**
 * AuthMe Admin — Dashboard Page
 *
 * Displays plugin overview, usage info, and quick links.
 *
 * @package AuthMe
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="authme-admin-wrap" id="authme-admin-dashboard">

    <!-- Page Header -->
    <div class="authme-admin-header">
        <h1 class="authme-admin-title">🔐 AuthMe Dashboard</h1>
        <p class="authme-admin-subtitle">WordPress Authentication Plugin by Art-Tech Fuzion</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="authme-admin-cards">

        <div class="authme-admin-card">
            <div class="authme-admin-card-icon">📊</div>
            <div class="authme-admin-card-content">
                <h3>Version</h3>
                <p class="authme-admin-card-value"><?php echo esc_html( AUTHME_VERSION ); ?></p>
            </div>
        </div>

        <div class="authme-admin-card">
            <div class="authme-admin-card-icon">👥</div>
            <div class="authme-admin-card-content">
                <h3>Total Customers</h3>
                <p class="authme-admin-card-value">
                    <?php
                    $customer_count = count( get_users( array( 'role' => 'customer' ) ) );
                    echo esc_html( $customer_count );
                    ?>
                </p>
            </div>
        </div>

        <div class="authme-admin-card">
            <div class="authme-admin-card-icon">🛡️</div>
            <div class="authme-admin-card-content">
                <h3>Security</h3>
                <p class="authme-admin-card-value">OTP Enabled</p>
            </div>
        </div>

    </div>

    <!-- ================================================ -->
    <!-- HOW TO USE — trigger link & direct URL            -->
    <!-- ================================================ -->
    <div class="authme-admin-section">
        <h2>📌 How to Use</h2>
        <p style="font-size:13px; color:#64748b; margin-bottom:20px;">The AuthMe overlay is <strong>automatically injected</strong> on every frontend page. Use any of the following methods to open it.</p>

        <div class="authme-admin-info-box" style="margin-bottom:16px;">
            <p><strong>1. Direct URL</strong></p>
            <p style="font-size:13px; color:#64748b; margin-top:4px;">Share this link directly — when visited, it redirects to your homepage and auto-opens the auth popup.</p>
            <code class="authme-admin-code"><?php echo esc_url( home_url( '/authme' ) ); ?></code>
        </div>

        <div class="authme-admin-info-box" style="margin-bottom:16px;">
            <p><strong>2. Trigger Link (HTML)</strong></p>
            <p style="font-size:13px; color:#64748b; margin-top:4px;">Add this HTML anywhere in your theme to create a clickable link that opens the auth popup.</p>
            <code class="authme-admin-code">&lt;a href="#" class="authme-trigger-link"&gt;Login / Register&lt;/a&gt;</code>
        </div>

        <div class="authme-admin-info-box" style="margin-bottom:16px;">
            <p><strong>3. JavaScript</strong></p>
            <p style="font-size:13px; color:#64748b; margin-top:4px;">Call this function from any <code>onclick</code> handler or custom JS to open the popup programmatically.</p>
            <code class="authme-admin-code">authmeOpenOverlay();</code>
        </div>

        <div class="authme-admin-info-box">
            <p><strong>⚠️ Important</strong></p>
            <p style="font-size:13px; color:#ef4444; margin-top:4px;">After activating the plugin, go to <strong>Settings → Permalinks</strong> and click <strong>Save Changes</strong> to flush rewrite rules. This is required for the <code>/authme</code> URL to work.</p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="authme-admin-section">
        <h2>Quick Links</h2>
        <div class="authme-admin-links-grid">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=authme-database' ) ); ?>" class="authme-admin-link-card">
                <span class="authme-admin-link-icon">🗄️</span>
                <span>Database Management</span>
            </a>
        </div>
    </div>

    <!-- File Paths Reference -->
    <div class="authme-admin-section">
        <h2>📁 File Paths</h2>
        <p style="font-size:13px; color:#64748b; margin-bottom:12px;">All file paths are centralized in <code>includes/assets-loader.php</code>. Use <code>AuthMe_Assets_Loader::dir('key')</code> or <code>AuthMe_Assets_Loader::url('key')</code> to access any path.</p>
    </div>

</div>
