# 🔐 AuthMe — WordPress Authentication Plugin

> A comprehensive WordPress authentication plugin with OTP-based two-factor verification for secure registration, login, and password reset.

**Version:** 1.5.0 | **Author:** [Art-Tech Fuzion](https://arttechfuzion.com) | **License:** Proprietary  
**Requires WordPress:** 5.0+ | **Requires PHP:** 7.4+

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Directory Structure](#directory-structure)
- [Installation](#installation)
- [How It Works](#how-it-works)
  - [Registration Flow](#registration-flow)
  - [Login Flow](#login-flow)
  - [Forgot Password Flow](#forgot-password-flow)
- [Database Schema](#database-schema)
  - [WordPress Core Tables](#wordpress-core-tables)
  - [Custom Table: wp_authme_otp_storage](#custom-table-wp_authme_otp_storage)
- [AJAX API Endpoints](#ajax-api-endpoints)
- [Class Reference](#class-reference)
- [Frontend Assets](#frontend-assets)
- [Admin Panel](#admin-panel)
- [Template Files](#template-files)
- [WooCommerce Integration](#woocommerce-integration)
- [Security](#security)
- [Cron Jobs](#cron-jobs)
- [Naming Conventions](#naming-conventions)
- [Error Messages](#error-messages)

---

## Overview

**AuthMe** replaces the default WordPress login/registration system with a secure, OTP-enforced overlay UI. When a non-logged-in user visits any frontend page, a hidden overlay is injected via `wp_footer`. The overlay automatically opens when the user navigates to `/authme` or when specific query parameter triggers are detected (e.g., WooCommerce checkout redirect).

All authentication happens through WordPress AJAX endpoints, keeping the page intact and providing a seamless single-page-like experience.

---

## Key Features

| Feature | Description |
|---|---|
| 🔍 Real-time field validation | Username and email availability checked live via AJAX as the user types |
| 📧 OTP Verification | 6-digit code with 60-second expiry sent via `wp_mail()` |
| 🛡️ Two-Factor Login | Password alone is not enough; a matching OTP is always required to complete login |
| 🔑 Forgot Password | OTP-verified password reset flow with "password changed" email notification |
| 👁️ Password Visibility Toggle | Eye icon for show/hide on all password fields |
| 🛒 WooCommerce Integration | Intercepts Checkout and My Account pages, redirects unauthenticated users to the popup |
| ⚙️ Admin Panel | Dashboard + Database health check with one-click table repair |
| 🔄 OTP Auto-Cleanup | WP-Cron runs twice daily to purge expired/verified OTP records |
| 🎨 Toast Notifications | Non-blocking top-right notifications for all user-facing feedback |

---

## Directory Structure

```
AuthMe/
├── authme.php                        # Main plugin file — bootstraps everything
│
├── includes/
│   ├── assets-loader.php             # Centralized registry of all CSS/JS file paths & URLs
│   ├── class-authme-db.php           # Database manager (create, check, repair tables)
│   ├── class-authme-email.php        # Email handler (OTP emails, password-changed email)
│   ├── class-authme-otp.php          # OTP generation, storage, verification, cleanup
│   └── class-authme-auth.php         # Authentication: register, login, forgot/reset password
│
├── admin/
│   ├── class-authme-admin.php        # Admin menu, pages, admin AJAX handlers
│   ├── assets/
│   │   ├── admin.css                 # Admin panel styles
│   │   └── admin.js                 # Admin panel JavaScript
│   └── templates/
│       ├── dashboard.php             # Admin dashboard page template
│       └── database.php             # Admin database management page template
│
├── templates/
│   ├── overlay.php                  # Main overlay container (wraps all auth forms)
│   ├── toaster.php                  # Toast notification container
│   ├── login.php                    # Login form HTML
│   ├── register.php                 # Registration form HTML
│   ├── otp.php                      # Standalone OTP input form
│   ├── forgot-password.php          # Forgot password form HTML
│   ├── new-password.php             # New password entry form HTML
│   ├── email-otp.php                # HTML email template for OTP delivery
│   └── email-password-changed.php   # HTML email template for password change notification
│
└── assets/
    ├── css/
    │   ├── global.css               # CSS variables, resets, utility classes
    │   ├── overlay.css              # Overlay modal styles
    │   ├── login.css                # Login form styles
    │   ├── register.css             # Registration form styles
    │   ├── otp.css                  # OTP input field styles
    │   ├── forgot-password.css      # Forgot password form styles
    │   ├── new-password.css         # New password form styles
    │   └── toaster.css              # Toast notification styles & animations
    └── js/
        ├── global.js                # Shared utilities: AJAX wrapper, validation helpers
        ├── overlay.js               # Overlay open/close logic + WooCommerce cart intercept
        ├── login.js                 # Login form logic: user lookup, password check, OTP
        ├── register.js              # Registration logic: real-time validation, OTP flow
        ├── otp.js                   # OTP countdown timer, resend handling
        ├── forgot-password.js       # Forgot password flow
        ├── new-password.js          # New password submission
        └── toaster.js               # Toast show/hide/stack/auto-dismiss logic
```

---

## Installation

1. Download the plugin zip file.
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and click **Install Now**, then **Activate**.
4. On activation the plugin automatically:
   - Creates the `wp_authme_otp_storage` database table.
   - Registers the `customer` WordPress role (if not already present).
   - Registers the `/authme` rewrite rule and flushes permalink rules.
   - Schedules the OTP cleanup WP-Cron event (`authme_otp_cleanup`, twice daily).

> **No shortcodes or page creation required.** The overlay is injected globally via `wp_footer` for all logged-out users.

---

## How It Works

### Registration Flow

```
User fills in form fields
    ↓
Real-time AJAX validation (username, email uniqueness)
    ↓
All fields valid → "Send OTP" button becomes active
    ↓
User clicks "Send OTP"
    └── POST: authme_send_otp (purpose=registration, user_data=JSON)
        ├── Invalidate any previous registration OTPs for this email
        ├── Generate random 6-digit code
        ├── Store in wp_authme_otp_storage with 60s expiry
        └── Send HTML email via wp_mail()
    ↓
60-second countdown timer displayed
    ↓
User enters OTP → POST: authme_verify_otp
    ├── Code mismatch → error toast
    ├── Expired → error toast ("request a new one")
    └── Valid → OTP marked is_verified=1, user_data returned to JS
    ↓
POST: authme_register_user (with user_data JSON)
    ├── Re-check username + email uniqueness
    ├── wp_insert_user() → creates entry in wp_users
    ├── Assigns 'customer' role
    └── Auto-login via wp_set_auth_cookie()
    ↓
User is now logged in. Page reloads / redirects to homepage.
```

**Key security detail:** The user account is created **only after successful OTP verification**. Incomplete registrations never produce a `wp_users` entry.

---

### Login Flow

```
User enters username or email
    ↓
POST: authme_check_user_exists
    ├── Auto-detects '@' → searches user_email; else searches user_login
    ├── Not found → error toast
    └── Found → password field revealed, user email stored in JS state
    ↓
User enters password → POST: authme_login_user
    ├── wp_check_password() verification
    ├── Mismatch → error toast
    └── Valid → "Send OTP" button activated (credentials verified, OTP pending)
    ↓
User clicks "Send OTP"
    └── POST: authme_send_otp (purpose=login, email=registered email)
        ├── Invalidate previous login OTPs for this email
        ├── Generate 6-digit code, store with 60s expiry
        └── Send "Login Verification Code" email
    ↓
User enters OTP → POST: authme_verify_otp (purpose=login)
    ↓
OTP valid → POST: authme_complete_login (or direct_login=true in authme_login_user)
    └── wp_set_auth_cookie() → user logged in
    ↓
Redirect to homepage.
```

---

### Forgot Password Flow

```
User enters email or username
    ↓
POST: authme_forgot_check_user
    └── Returns registered email if user exists
    ↓
POST: authme_send_otp (purpose=password_reset)
    ↓
User enters OTP → POST: authme_verify_otp (purpose=password_reset)
    ↓
User enters new password → POST: authme_reset_password
    ├── wp_set_password() updates the hash in wp_users
    └── Sends "Your Password Was Changed" notification email
    ↓
User redirected to login form.
```

---

## Database Schema

### WordPress Core Tables

AuthMe reads from and writes to two standard WordPress tables.

#### `wp_users`

| Column | Type | AuthMe Usage |
|---|---|---|
| `ID` | BIGINT(20) | Auto-generated primary key |
| `user_login` | VARCHAR(60) | Username — must start with letter, 3–20 alphanumeric chars, unique |
| `user_pass` | VARCHAR(255) | WordPress-hashed password (bcrypt) |
| `user_nicename` | VARCHAR(50) | Set to `sanitize_title(username)` — used in URLs |
| `user_email` | VARCHAR(100) | Email — validated format + unique |
| `user_registered` | DATETIME | Timestamp set by `wp_insert_user()` on successful OTP verification |
| `user_status` | INT(11) | `0` for active accounts |

#### `wp_usermeta`

| Column | Type | AuthMe Usage |
|---|---|---|
| `umeta_id` | BIGINT(20) | Auto-generated |
| `user_id` | BIGINT(20) | FK → `wp_users.ID` |
| `meta_key` | VARCHAR(255) | `wp_capabilities` |
| `meta_value` | LONGTEXT | Serialized: `a:1:{s:8:"customer";b:1;}` |

All new users are assigned the `customer` role. This role is created during plugin activation if it does not already exist.

---

### Custom Table: `wp_authme_otp_storage`

This is the only custom database table created by the plugin. It stores OTP codes for all three flows: registration, login, and password reset.

```sql
CREATE TABLE wp_authme_otp_storage (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    email       VARCHAR(100) NOT NULL,
    otp_code    VARCHAR(6)   NOT NULL,
    purpose     VARCHAR(20)  NOT NULL DEFAULT 'registration',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_verified TINYINT(1)   NOT NULL DEFAULT 0,
    user_data   TEXT,
    PRIMARY KEY (id),
    KEY email_purpose (email, purpose)
);
```

#### Column Details

| Column | Type | Nullable | Description |
|---|---|---|---|
| `id` | INT(11) AUTO_INCREMENT | No | Primary key |
| `email` | VARCHAR(100) | No | Recipient email address |
| `otp_code` | VARCHAR(6) | No | 6-digit zero-padded numeric code (e.g., `042813`) |
| `purpose` | VARCHAR(20) | No | One of: `registration`, `login`, `password_reset` |
| `created_at` | TIMESTAMP | No | Time the OTP was generated |
| `expires_at` | TIMESTAMP | No | `created_at + 60 seconds` |
| `is_verified` | TINYINT(1) | No | `0` = pending, `1` = used/verified |
| `user_data` | TEXT | Yes | JSON blob of `{username, email, password}` — only populated for `purpose=registration`, NULL otherwise |

#### Index

- **`email_purpose (email, purpose)`** — composite index used by `SELECT` queries that look up the latest OTP by email + purpose.

#### Row Lifecycle

```
INSERT on "Send OTP" click
    ↓
Previous unverified rows for same (email, purpose) are DELETED first
    ↓
Row is_verified=0 while waiting
    ↓
Successful verify → UPDATE is_verified=1
    ↓
WP-Cron cleanup (twice daily):
    ├── DELETE WHERE is_verified=1           (all used OTPs)
    └── DELETE WHERE is_verified=0
            AND expires_at < (NOW - 1 hour)  (stale unverified OTPs)
```

---

## AJAX API Endpoints

All endpoints are registered for both `wp_ajax_` (logged-in) and `wp_ajax_nopriv_` (public). Every endpoint validates a WordPress nonce (`authme_nonce`) before processing.

| Action | Handler Class | Purpose |
|---|---|---|
| `authme_check_username` | `AuthMe_Auth` | Check username availability (registration) |
| `authme_check_email` | `AuthMe_Auth` | Check email availability (registration) |
| `authme_check_user_exists` | `AuthMe_Auth` | Look up user by username or email (login/forgot) |
| `authme_login_user` | `AuthMe_Auth` | Validate credentials; with `direct_login=true` sets auth cookie |
| `authme_complete_login` | `AuthMe_Auth` | Finalize login after OTP verification |
| `authme_register_user` | `AuthMe_Auth` | Create user account after OTP verification |
| `authme_forgot_check_user` | `AuthMe_Auth` | Look up account for forgot-password flow |
| `authme_reset_password` | `AuthMe_Auth` | Update password + send notification email |
| `authme_send_otp` | `AuthMe_OTP` | Generate, store, and email a new OTP |
| `authme_verify_otp` | `AuthMe_OTP` | Verify submitted OTP code |

### Endpoint Details

#### `authme_send_otp`

**POST params:**

| Param | Required | Description |
|---|---|---|
| `email` | Yes | Recipient email address |
| `purpose` | Yes | `registration`, `login`, or `password_reset` |
| `user_data` | Only for registration | JSON string: `{"username":"...","email":"...","password":"..."}` |
| `nonce` | Yes | WordPress nonce value |

**Success response:**
```json
{ "success": true, "data": { "message": "OTP has been sent to your email.", "expiry": 60 } }
```

#### `authme_verify_otp`

**POST params:** `email`, `otp_code`, `purpose`, `nonce`

**Success response (registration):**
```json
{ "success": true, "data": { "message": "OTP verified successfully.", "user_data": "{...}" } }
```

**Success response (login/password_reset):**
```json
{ "success": true, "data": { "message": "OTP verified successfully." } }
```

#### `authme_register_user`

**POST params:** `user_data` (JSON string), `nonce`

**Success response:**
```json
{ "success": true, "data": { "message": "Registration successful! Welcome aboard." } }
```

#### `authme_login_user`

**POST params:** `identifier`, `password`, `remember` (`true`/`false`), `direct_login` (`true`/`false`), `nonce`

**Success response (direct login):**
```json
{ "success": true, "data": { "message": "Login successful! Welcome back, John." } }
```

**Success response (OTP login path):**
```json
{ "success": true, "data": { "message": "Credentials verified. Sending OTP…", "email": "user@example.com", "user_id": 42, "remember": false } }
```

---

## Class Reference

### `AuthMe_DB`
**File:** `includes/class-authme-db.php`

Manages the custom database table.

| Method | Visibility | Description |
|---|---|---|
| `__construct()` | public | Sets `$table_name` using `$wpdb->prefix` |
| `create_tables()` | public | Creates `wp_authme_otp_storage` using `dbDelta()` |
| `check_table_status()` | public | Returns array: `table_exists`, `columns`, `missing_columns`, `all_good` |
| `get_table_name()` | public | Returns the full table name string |

---

### `AuthMe_OTP`
**File:** `includes/class-authme-otp.php`

Handles all OTP lifecycle operations.

| Method | Visibility | Description |
|---|---|---|
| `__construct()` | public | Sets `$table_name` |
| `ajax_send_otp()` | public | AJAX handler: generates, stores, and emails OTP |
| `ajax_verify_otp()` | public | AJAX handler: validates submitted OTP |
| `invalidate_previous_otps($email, $purpose)` | private | Deletes all unverified OTPs for an email+purpose pair |
| `cleanup_expired_otps()` | public | Deletes verified OTPs and expired unverified OTPs (called by WP-Cron) |

**Constants:**

| Constant | Value | Description |
|---|---|---|
| `OTP_EXPIRY_SECONDS` | `60` | OTP validity window in seconds |

---

### `AuthMe_Auth`
**File:** `includes/class-authme-auth.php`

Handles all user authentication AJAX endpoints.

| Method | Visibility | Description |
|---|---|---|
| `ajax_check_username()` | public | Validates format + uniqueness of username |
| `ajax_check_email()` | public | Validates format + uniqueness of email |
| `ajax_check_user_exists()` | public | Finds user by email or username |
| `ajax_login_user()` | public | Validates credentials; optionally sets auth cookie immediately |
| `ajax_complete_login()` | public | Sets auth cookie after OTP-verified login |
| `ajax_register_user()` | public | Creates `wp_users` entry + auto-logs in after OTP |
| `ajax_forgot_check_user()` | public | Looks up user for forgot-password flow |
| `ajax_reset_password()` | public | Updates password hash + sends notification email |

---

### `AuthMe_Email`
**File:** `includes/class-authme-email.php`

Sends all outgoing emails using `wp_mail()`.

| Method | Visibility | Description |
|---|---|---|
| `send_otp_email($to, $otp, $purpose)` | public | Sends OTP email; subject varies by purpose |
| `get_email_template($otp, $purpose)` | private | Renders `templates/email-otp.php` via output buffering |
| `send_password_changed_email($to, $name)` | public | Sends password-changed notification |
| `get_password_changed_template($name)` | private | Renders `templates/email-password-changed.php` |

---

### `AuthMe_Assets_Loader`
**File:** `includes/assets-loader.php`

Single source of truth for all asset paths and URLs.

| Method | Visibility | Description |
|---|---|---|
| `get_paths()` | public static | Returns associative array of all CSS/JS file paths and public URLs |
| `enqueue_frontend()` | public static | Enqueues frontend CSS/JS; called on `wp_enqueue_scripts` |

---

### `AuthMe_Admin`
**File:** `admin/class-authme-admin.php`

Registers and renders the WordPress admin interface.

| Method | Visibility | Description |
|---|---|---|
| `__construct()` | public | Hooks `admin_menu`, `admin_enqueue_scripts`, admin AJAX actions |
| `register_admin_menus()` | public | Adds "AuthMe" top-level menu and "Database" sub-menu |
| `enqueue_admin_assets($hook)` | public | Enqueues `admin.css` + `admin.js` only on AuthMe pages |
| `render_dashboard()` | public | Renders `admin/templates/dashboard.php` |
| `render_database()` | public | Renders `admin/templates/database.php` |
| `ajax_check_db_status()` | public | Admin AJAX: returns current table health |
| `ajax_create_tables()` | public | Admin AJAX: creates/repairs the OTP table |

---

## Frontend Assets

### CSS Variables (`assets/css/global.css`)

```css
:root {
    --authme-primary:    #0073aa;
    --authme-secondary:  #005177;
    --authme-success:    #28a745;
    --authme-error:      #dc3232;
    --authme-warning:    #ffb900;
    --authme-text:       #333333;
    --authme-text-light: #666666;
    --authme-border:     #dddddd;
    --authme-bg:         #ffffff;
    --authme-bg-light:   #f8f9fa;
    --authme-radius:     4px;
    --authme-shadow:     0 1px 3px rgba(0,0,0,0.1);
}
```

### JavaScript Files

| File | Responsibility |
|---|---|
| `global.js` | Shared AJAX wrapper, input sanitization helpers, utility functions |
| `overlay.js` | Opens/closes the modal overlay; intercepts WooCommerce "Proceed to Checkout" button |
| `register.js` | Debounced AJAX username/email checks; password strength indicator; OTP submit |
| `login.js` | User lookup; password verification; OTP step coordination |
| `otp.js` | 60-second countdown timer; "Resend OTP" activation after expiry |
| `forgot-password.js` | User lookup and OTP trigger for forgot-password flow |
| `new-password.js` | Password strength validation and reset submission |
| `toaster.js` | Toast queue, auto-dismiss (default 5000ms), close button, stacking |

### Toast Notification Types

| Type | Icon | Color |
|---|---|---|
| `success` | ✅ | Green |
| `error` | ❌ | Red |
| `warning` | ⚠️ | Yellow |
| `info` | ℹ️ | Blue |

---

## Admin Panel

### Menu Structure

```
WordPress Admin Sidebar
└── 🔐 AuthMe                    (slug: authme)
    └── Database                 (slug: authme-database)
```

### Database Page

The database page performs a live health check on `wp_authme_otp_storage`:

- Displays whether the table exists
- Lists all 8 required columns with ✅/❌ status
- Provides a **"Create / Repair Table"** button that calls `dbDelta()` to create missing tables or add missing columns without data loss

### Settings Link

A **Settings** link is automatically added to the AuthMe row on `wp-admin/plugins.php`, pointing directly to `wp-admin/admin.php?page=authme`.

---

## Template Files

| Template | Used For |
|---|---|
| `templates/overlay.php` | Outer modal shell injected into every frontend page via `wp_footer` |
| `templates/toaster.php` | Toast container div, injected alongside the overlay |
| `templates/login.php` | Login form: identifier field, password field, OTP section |
| `templates/register.php` | Registration form: username, email, password, confirm password, OTP section |
| `templates/otp.php` | Standalone OTP entry screen |
| `templates/forgot-password.php` | Forgot password: email/username input |
| `templates/new-password.php` | New password + confirm fields after OTP verification |
| `templates/email-otp.php` | HTML email with OTP code, styled for all three purposes |
| `templates/email-password-changed.php` | HTML notification email sent after successful password reset |

---

## WooCommerce Integration

When WooCommerce is active, AuthMe intercepts page templates for unauthenticated users:

| Page | Behavior |
|---|---|
| **Cart page** | JS in `overlay.js` intercepts the "Proceed to Checkout" button click and opens the auth popup before allowing checkout navigation |
| **Checkout page** | Non-logged-in users are redirected back to the cart URL via `wp_safe_redirect()` |
| **My Account page** | Non-logged-in users are redirected to `/?authme_open=1`, triggering the popup on the homepage |

This logic lives in `authme_woo_intercept_pages()` hooked to `template_redirect` and runs only when `class_exists('WooCommerce')` returns true.

---

## Security

| Area | Measure |
|---|---|
| **CSRF** | All AJAX endpoints call `check_ajax_referer('authme_nonce', 'nonce')` |
| **SQL Injection** | All database queries use `$wpdb->prepare()` with typed placeholders |
| **XSS** | All user inputs sanitized with `sanitize_user()`, `sanitize_email()`, `sanitize_text_field()`, `wp_unslash()`; output escaped with `esc_html()` |
| **Password storage** | Passwords hashed via WordPress's `wp_insert_user()` which uses phpass |
| **Password strength** | Enforced client-side and server-side: min 8 chars, uppercase, lowercase, number, special character |
| **OTP security** | 6-digit code, 60-second TTL, invalidated on resend, deleted after use |
| **Incomplete registration** | No `wp_users` row created until OTP is successfully verified |
| **Direct access** | All PHP files check `defined('ABSPATH')` and `exit` if accessed directly |
| **Admin AJAX** | Admin endpoints additionally require `manage_options` capability via WordPress's `wp_ajax_` (no-priv variant not registered for admin actions) |

---

## Cron Jobs

| Hook | Schedule | Callback | Action |
|---|---|---|---|
| `authme_otp_cleanup` | `twicedaily` | `authme_run_otp_cleanup()` | Deletes all `is_verified=1` rows; deletes `is_verified=0` rows where `expires_at < NOW - 1 hour` |

The cron is scheduled on plugin activation and unscheduled on deactivation.

---

## Virtual `/authme` URL

The plugin registers a WordPress rewrite rule so that `yoursite.com/authme` works as a clean URL:

```
/authme  →  index.php?authme_page=1
```

The `template_redirect` hook then handles this query var:

- **If logged in** → `wp_safe_redirect(home_url())` — no popup needed
- **If logged out** → `wp_safe_redirect(home_url('?authme_open=1'))` → homepage with auto-open trigger

The `wp_footer` hook detects `?authme_open=1` and injects a small `<script>` that calls `authmeOpenOverlay()` on `DOMContentLoaded`.

---

## Naming Conventions

All identifiers in the plugin use the `authme` prefix to prevent conflicts with other plugins and themes.

| Type | Pattern | Example |
|---|---|---|
| PHP functions | `authme_*` | `authme_activate_plugin()`, `authme_run_otp_cleanup()` |
| PHP classes | `AuthMe_*` | `AuthMe_Auth`, `AuthMe_OTP`, `AuthMe_DB` |
| Database tables | `wp_authme_*` | `wp_authme_otp_storage` |
| AJAX actions | `authme_*` | `authme_send_otp`, `authme_verify_otp` |
| CSS IDs | `#authme-*` | `#authme-login-form`, `#authme-overlay` |
| CSS classes | `.authme-*` | `.authme-btn-primary`, `.authme-input-field` |
| JS globals | `authme*` | `authmeOpenOverlay()`, `authmeShowToast()` |
| WP options / meta | `authme_*` | `authme_nonce` |
| WordPress hooks | `authme_*` | `authme_otp_cleanup` |

---

## Error Messages

| Trigger | Message Displayed |
|---|---|
| Username already taken | `Username not available.` |
| Username starts with number | `Username must start with an alphabet character.` |
| Username wrong length/chars | `Username must be 3–20 alphanumeric characters.` |
| Email already registered | `Email already registered.` |
| Invalid email format | `Please enter a valid email address.` |
| Passwords do not match | *(client-side validation, inline indicator)* |
| Weak password | *(client-side strength indicator)* |
| User not found at login | `User not found. Please check your credentials.` |
| Wrong password | `Incorrect password. Please try again.` |
| OTP expired | `OTP has expired. Please request a new one.` |
| Wrong OTP entered | `Invalid OTP. Please try again.` |
| No OTP record found | `No OTP found. Please request a new one.` |
| Email send failure | `Failed to send OTP email. Please try again.` |
| DB insert failure | `Failed to store OTP. Please try again.` |
| User not found (forgot pw) | `No account found with this detail. Please create one.` |
| New password too short | `Password must be at least 8 characters.` |

---

## Developer Notes

- **`dbDelta()` is used for table creation**, so re-running `create_tables()` is safe — it only adds missing structures and never drops data.
- **Assets are version-stamped using `filemtime()`** — cache busting is automatic; no manual version bumps needed after editing CSS/JS files.
- **The overlay is injected only for non-logged-in users** — `is_user_logged_in()` check in `authme_inject_overlay_in_footer()` prevents unnecessary DOM injection for authenticated sessions.
- **`wp_mail()` is used for all emails** — this means full compatibility with any SMTP plugin (WP Mail SMTP, FluentSMTP, etc.) that hooks into `wp_mail`.
- **Admin AJAX actions use a separate nonce** (`authme_admin_nonce`) from the frontend nonce (`authme_nonce`).

---

*© 2026 Art-Tech Fuzion. All rights reserved. — [arttechfuzion.com](https://arttechfuzion.com)*