/**
 * AuthMe Admin JavaScript
 *
 * Handles database status checking and table creation
 * via AJAX on the admin Database Management page.
 *
 * @package AuthMe
 */

(function () {
    'use strict';

    /* ── Helpers ──────────────────────────── */

    /**
     * Show a quick toast notification in the admin area.
     *
     * @param {string} message Toast message text.
     * @param {string} type    'success' or 'error'.
     */
    function showAdminToast(message, type) {
        // Remove any existing toast
        var existing = document.querySelector('.authme-admin-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'authme-admin-toast authme-admin-toast-' + type;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 4000);
    }

    /* ── DB Status Renderer ──────────────── */

    /**
     * Render the database status table into the container.
     *
     * @param {object} data Status data from the server.
     */
    function renderStatusTable(data) {
        var container = document.getElementById('authme-db-status-container');
        if (!container) return;

        var html = '';

        // Table existence row
        html += '<table class="authme-db-status-table">';
        html += '<thead><tr><th>Item</th><th>Status</th></tr></thead>';
        html += '<tbody>';

        html += '<tr>';
        html += '<td><strong>wp_authme_otp_storage</strong> (Table)</td>';
        html += '<td class="' + (data.table_exists ? 'authme-status-ok' : 'authme-status-missing') + '">';
        html += data.table_exists ? '✅ Exists' : '❌ Missing';
        html += '</td></tr>';

        // Column rows
        if (data.table_exists && data.columns) {
            var columns = data.columns;
            for (var col in columns) {
                if (columns.hasOwnProperty(col)) {
                    html += '<tr>';
                    html += '<td>&nbsp;&nbsp;&nbsp;&nbsp;↳ <code>' + col + '</code></td>';
                    html += '<td class="' + (columns[col] ? 'authme-status-ok' : 'authme-status-missing') + '">';
                    html += columns[col] ? '✅ OK' : '❌ Missing';
                    html += '</td></tr>';
                }
            }
        }

        html += '</tbody></table>';

        container.innerHTML = html;
    }

    /* ── AJAX: Fetch DB Status ───────────── */

    function fetchDbStatus() {
        var container = document.getElementById('authme-db-status-container');
        if (!container) return;

        container.innerHTML = '<p class="authme-admin-loading">Checking database status…</p>';

        var formData = new FormData();
        formData.append('action', 'authme_admin_check_db');
        formData.append('nonce', authme_admin.nonce);

        fetch(authme_admin.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            if (result.success) {
                renderStatusTable(result.data);
            } else {
                container.innerHTML = '<p style="color:#dc2626;">Failed to check database status.</p>';
            }
        })
        .catch(function () {
            container.innerHTML = '<p style="color:#dc2626;">Network error. Please try again.</p>';
        });
    }

    /* ── AJAX: Create Tables ─────────────── */

    function createTables() {
        var btn = document.getElementById('authme-btn-create-tables');
        if (!btn) return;

        btn.disabled = true;
        btn.textContent = 'Creating…';

        var formData = new FormData();
        formData.append('action', 'authme_admin_create_tables');
        formData.append('nonce', authme_admin.nonce);

        fetch(authme_admin.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            btn.disabled = false;
            btn.textContent = 'Create / Update Tables';

            if (result.success) {
                showAdminToast(result.data.message, 'success');
                fetchDbStatus(); // Refresh the status table
            } else {
                showAdminToast(result.data.message || 'An error occurred.', 'error');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Create / Update Tables';
            showAdminToast('Network error. Please try again.', 'error');
        });
    }

    /* ── Event Listeners ─────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-check status on page load (only on the database page)
        if (document.getElementById('authme-admin-database')) {
            fetchDbStatus();
        }

        // Create Tables button
        var createBtn = document.getElementById('authme-btn-create-tables');
        if (createBtn) {
            createBtn.addEventListener('click', createTables);
        }

        // Refresh Status button
        var refreshBtn = document.getElementById('authme-btn-refresh-status');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', fetchDbStatus);
        }
    });

})();
