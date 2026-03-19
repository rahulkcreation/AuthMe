# 🔐 AuthMe

### WordPress Authentication Plugin

**Product Requirements Document**

---

**Version:** 1.0.0  
**Author:** Art-Tech Fuzion  
**Website:** [arttechfuzion.com](https://arttechfuzion.com)  
**Document Date:** March 19, 2026

---

## 📋 Table of Contents

- [1. Executive Summary](#1-executive-summary)
- [2. Project Overview](#2-project-overview)
  - [2.1 Plugin Information](#21-plugin-information)
  - [2.2 Plugin Architecture](#22-plugin-architecture)
  - [2.3 Naming Conventions](#23-naming-conventions)
- [3. Database Schema](#3-database-schema)
  - [3.1 WordPress Core Tables Usage](#31-wordpress-core-tables-usage)
  - [3.2 Custom Plugin Table](#32-custom-plugin-table)
- [4. Functional Requirements](#4-functional-requirements)
  - [4.1 Registration Form](#41-registration-form)
  - [4.2 Login Form](#42-login-form)
  - [4.3 Logged-in User Handling](#43-logged-in-user-handling)
- [5. Admin Panel Features](#5-admin-panel-features)
- [6. Frontend Components](#6-frontend-components)
- [7. Security Considerations](#7-security-considerations)
- [8. AJAX API Endpoints](#8-ajax-api-endpoints)
- [9. Template Files](#9-template-files)
- [10. User Role Assignment](#10-user-role-assignment)
- [11. Error Handling](#11-error-handling)
- [12. Testing Requirements](#12-testing-requirements)
- [13. Acceptance Criteria](#13-acceptance-criteria)
- [14. Document Control](#14-document-control)

---

## 1. Executive Summary

**AuthMe** is a comprehensive WordPress authentication plugin developed by **Art-Tech Fuzion** that provides secure user registration and login functionality with industry-standard security practices. The plugin implements **OTP (One-Time Password) verification** for both registration and login processes, real-time field validation, and seamlessly integrates with the WordPress ecosystem using native functions and conventions.

The plugin is designed with a focus on **security**, **user experience**, and **ease of configuration**. All function names, class names, CSS IDs, and CSS classes utilize the `authme` prefix to ensure conflict-free operation across different WordPress installations. The email functionality leverages WordPress's native `wp_mail()` function, ensuring compatibility with existing server configurations and SMTP plugins.

### ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔍 **Real-time Validation** | Username and email availability checking |
| 👁️ **Password Visibility Toggle** | Eye icon for show/hide password |
| 📧 **OTP Verification** | 60-second expiry timer for both registration and login |
| 🛡️ **Two-Factor Authentication** | Login requires OTP for enhanced security |
| ⚙️ **Admin Panel** | Comprehensive dashboard for database management |
| 🔒 **Incomplete Registration Prevention** | Accounts created only after OTP verification |

---

## 2. Project Overview

### 2.1 Plugin Information

| Attribute | Value |
|-----------|-------|
| **Plugin Name** | **AuthMe** |
| **Author** | Art-Tech Fuzion |
| **Website** | arttechfuzion.com |
| **Version** | 1.0.0 |
| **Prefix** | `authme_` |
| **Minimum WordPress Version** | 5.0+ |
| **Minimum PHP Version** | 7.4+ |

### 2.2 Plugin Architecture

The plugin architecture follows a **modular design pattern** that ensures clean code organization and easy maintenance. All necessary files and folders are included from the main plugin file, with templates, assets, and includes properly separated.

#### 📁 Directory Structure

```
authme/
├── 📄 authme.php              # Main plugin file with initialization code
├── 📂 templates/              # All template files (register.php, login.php)
├── 📂 assets/
│   ├── 📂 css/               # CSS files (global.css, register.css, login.css, toaster.css)
│   ├── 📂 js/                # JavaScript files (global.js, register.js, login.js, toaster.js)
│   └── 📂 images/            # Image assets (icons, logos)
├── 📂 includes/               # PHP class files and helper functions
├── 📂 admin/                  # Admin panel pages and settings
└── 📂 screens/                # Design reference screens and mockups
```

### 2.3 Naming Conventions

All identifiers within the plugin utilize the `authme` prefix to ensure uniqueness and prevent conflicts with other WordPress plugins and themes.

| Type | Examples |
|------|----------|
| **Function names** | `authme_register_user()`, `authme_validate_email()`, `authme_send_otp()` |
| **Class names** | `AuthMe_Auth_Handler`, `AuthMe_OTP_Manager`, `AuthMe_Email_Handler` |
| **CSS IDs** | `#authme-register-form`, `#authme-login-form`, `#authme-otp-field` |
| **CSS Classes** | `.authme-btn-primary`, `.authme-input-field`, `.authme-error-message` |
| **Database tables** | `wp_authme_otp_storage` |
| **AJAX actions** | `authme_check_username`, `authme_send_otp`, `authme_verify_otp` |

---

## 3. Database Schema

The plugin creates **one custom database table** in the WordPress database and utilizes WordPress core tables (`wp_users` and `wp_usermeta`) for user data storage.

### 3.1 WordPress Core Tables Usage

#### 📊 wp_users Table

| Column Name | Data Type | Plugin Usage Description |
|-------------|-----------|--------------------------|
| `ID` | BIGINT(20) | Unique user identifier, auto-generated by WordPress |
| `user_login` | VARCHAR(60) | Username - validated for uniqueness, must start with alphabet character |
| `user_pass` | VARCHAR(255) | Hashed password using WordPress hash format |
| `user_nicename` | VARCHAR(50) | URL-friendly name - set to same value as user_login |
| `user_email` | VARCHAR(100) | Email address - validated for format and uniqueness |
| `user_registered` | DATETIME | Registration timestamp - set upon successful OTP verification |
| `user_status` | INT(11) | User status flag - 0 for active accounts |

#### 📊 wp_usermeta Table

| Column Name | Data Type | Plugin Usage Description |
|-------------|-----------|--------------------------|
| `umeta_id` | BIGINT(20) | Unique meta identifier, auto-generated |
| `user_id` | BIGINT(20) | Foreign key reference to wp_users.ID |
| `meta_key` | VARCHAR(255) | Key: `wp_capabilities` for role assignment |
| `meta_value` | LONGTEXT | Serialized array: `a:1:{s:8:"customer";b:1;}` |

### 3.2 Custom Plugin Table

#### 📊 wp_authme_otp_storage Table

The OTP storage table manages one-time password codes and their associated metadata for **both registration and login processes**.

| Column Name | Data Type | Description |
|-------------|-----------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary key identifier |
| `email` | VARCHAR(100) | Email address associated with the OTP |
| `otp_code` | VARCHAR(6) | 6-digit numeric one-time password code |
| `purpose` | VARCHAR(20) | OTP purpose: `registration` or `login` |
| `created_at` | TIMESTAMP | Timestamp of OTP generation |
| `expires_at` | TIMESTAMP | Expiry timestamp (created_at + 60 seconds) |
| `is_verified` | TINYINT(1) | Verification status: 0=pending, 1=verified |
| `user_data` | TEXT | JSON-encoded temporary user data for pending registrations |

---

## 4. Functional Requirements

### 4.1 Registration Form

The registration form implements a **multi-step verification process** where user details are validated first, followed by OTP verification, and finally account creation.

#### 📝 Form Fields and Validation

| Field Name | Input Type | Validation Rules |
|------------|------------|------------------|
| **Username** | Text Input | ✅ Required<br>✅ Must start with alphabet character<br>✅ Can contain alphanumeric characters<br>✅ Length: 3-20 characters<br>✅ Must be unique in `wp_users.user_login` |
| **Email** | Email Input | ✅ Required<br>✅ Valid email format (regex validation)<br>✅ Must be unique in `wp_users.user_email` |
| **Password** | Password Input | ✅ Required<br>✅ Minimum 8 characters<br>✅ At least 1 uppercase letter<br>✅ At least 1 lowercase letter<br>✅ At least 1 number<br>✅ At least 1 special character<br>✅ Eye toggle icon for visibility |
| **Confirm Password** | Password Input | ✅ Required<br>✅ Must match Password field exactly<br>✅ Eye toggle icon for visibility |
| **OTP Code** | Number Input | ✅ 6-digit numeric code<br>✅ 60-second countdown timer<br>✅ Resend OTP option after expiry |

#### 🔄 Real-time Validation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    REGISTRATION VALIDATION FLOW                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Username Validation                                          │
│     └─► AJAX request → Check wp_users.user_login                │
│         ├─► Exists → Red "Username not available"               │
│         └─► Available → Green "Username available"              │
│                                                                 │
│  2. Email Validation                                             │
│     └─► Format check + AJAX request → Check wp_users.user_email │
│         ├─► Invalid format → "Please enter valid email"         │
│         ├─► Exists → Red "Email already registered"             │
│         └─► Available → Green "Email available"                 │
│                                                                 │
│  3. Password Validation                                          │
│     └─► Real-time strength check                                │
│         └─► Visual indicator shows password strength            │
│                                                                 │
│  4. Send OTP Button                                              │
│     └─► Disabled until ALL fields pass validation               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 📧 Registration OTP Verification Process

1. **OTP Generation**  
   When the user clicks 'Send OTP', the server generates a random 6-digit OTP code stored in `wp_authme_otp_storage` with `purpose='registration'` and a 60-second expiry timestamp.

2. **Email Delivery**  
   The OTP is sent to the user's email using WordPress's native `wp_mail()` function. The email template features a professional design with the OTP code prominently displayed.

3. **Timer Display**  
   A 60-second countdown timer displays on the frontend, informing the user of the OTP validity period.

4. **Resend Option**  
   After the timer expires, a 'Resend OTP' button appears. Clicking this generates a new OTP and invalidates the previous one.

5. **Verification**  
   When the user enters the OTP, the server verifies that it matches, has `purpose='registration'`, and has not expired.

#### ✅ Account Creation Process

Account creation occurs **only after successful OTP verification**. This security measure ensures that incomplete registrations do not create clutter in the database.

```
┌────────────────────────────────────────────────────────────┐
│                  ACCOUNT CREATION FLOW                     │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  1. User data stored temporarily in JSON format            │
│     └─► wp_authme_otp_storage.user_data column             │
│                                                            │
│  2. Upon OTP verification:                                 │
│     ├─► Create entry in wp_users                           │
│     ├─► Set user_login = username                          │
│     ├─► Set user_nicename = username (URL-friendly)        │
│     ├─► Set user_email = email                             │
│     ├─► Set user_pass = WordPress-hashed password          │
│     ├─► Set user_registered = current timestamp            │
│     └─► Create wp_usermeta entry for 'customer' role       │
│                                                            │
│  3. Mark OTP as verified (is_verified=1)                   │
│                                                            │
│  4. Auto-login the user                                    │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

---

### 4.2 Login Form

The login form features a **unified input field design** where users can enter either their username or email address. The system automatically detects the input format and performs validation accordingly.

> ⚠️ **Important:** The login process requires OTP verification to ensure that only authorized users with access to the registered email can complete the authentication.

#### 📝 Form Fields

| Field Name | Input Type | Validation Rules |
|------------|------------|------------------|
| **Username/Email** | Text Input | ✅ Required<br>✅ System auto-detects format (email or username)<br>✅ Searches `user_email` or `user_login` accordingly |
| **Password** | Password Input | ✅ Required<br>✅ WordPress password verification using `wp_check_password()`<br>✅ Eye toggle for visibility |
| **OTP Code** | Number Input | ✅ 6-digit numeric code<br>✅ 60-second countdown timer<br>✅ Resend OTP option after expiry |

#### 🔄 Login Credential Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      LOGIN CREDENTIAL FLOW                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. User enters username or email                               │
│     └─► System auto-detects format based on '@' symbol          │
│                                                                 │
│  2. Real-time validation                                        │
│     ├─► Email format → Search wp_users.user_email               │
│     └─► Username format → Search wp_users.user_login            │
│                                                                 │
│  3. User not found?                                             │
│     └─► Error: "User not found. Please check your credentials." │
│                                                                 │
│  4. User found?                                                 │
│     └─► Enable password field for input                         │
│                                                                 │
│  5. Password validation                                         │
│     └─► wp_check_password() against stored hash                 │
│                                                                 │
│  6. Password match?                                             │
│     └─► Enable 'Send OTP' button                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 🔐 Login OTP Verification Process

Similar to registration, the login process requires OTP verification to ensure that only authorized users with access to the registered email can complete the authentication. This adds an **additional layer of security beyond just password verification**.

```
┌─────────────────────────────────────────────────────────────────┐
│                    LOGIN OTP VERIFICATION FLOW                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. OTP Generation for Login                                    │
│     ├─► User clicks 'Send OTP' after password validation        │
│     ├─► Server generates random 6-digit OTP                     │
│     ├─► Store in wp_authme_otp_storage with:                    │
│     │   ├─► purpose = 'login'                                   │
│     │   ├─► email = user's registered email                     │
│     │   └─► expires_at = current_time + 60 seconds              │
│     └─► Previous login OTPs for this email are invalidated      │
│                                                                 │
│  2. Email Delivery                                              │
│     ├─► OTP sent to user's registered email                     │
│     ├─► Uses WordPress wp_mail() function                       │
│     └─► Email indicates "Login Verification OTP"                │
│                                                                 │
│  3. Timer Display                                               │
│     ├─► 60-second countdown timer on frontend                   │
│     └─► OTP input field active during this time                 │
│                                                                 │
│  4. Resend Option                                               │
│     ├─► After timer expires, 'Resend OTP' appears               │
│     └─► New OTP invalidates previous one                        │
│                                                                 │
│  5. Verification                                                │
│     Server verifies:                                            │
│     ├─► OTP code matches                                        │
│     ├─► purpose = 'login'                                       │
│     ├─► email matches user's registered email                   │
│     └─► OTP has not expired                                     │
│                                                                 │
│  6. Login Completion                                            │
│     ├─► User logged in via wp_set_auth_cookie()                 │
│     ├─► Redirect to homepage                                    │
│     └─► OTP marked as verified (is_verified=1)                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 🛡️ Login Security Benefits

| Benefit | Description |
|---------|-------------|
| 🔐 **Two-Factor Authentication** | Even if an attacker obtains a user's password, they cannot login without access to the registered email |
| 🚫 **Credential Stuffing Prevention** | Automated attacks using leaked passwords are ineffective without email access |
| 📨 **Login Notification** | Users receive an email notification whenever a login attempt occurs |
| ⏱️ **Time-Limited Access** | The 60-second OTP window limits the time an attacker has to intercept and use the code |

---

### 4.3 Logged-in User Handling

The plugin implements a check to determine if the current user is logged in using WordPress's `is_user_logged_in()` function.

```
┌───────────────────────────────────────────────────┐
│         LOGGED-IN USER HANDLING                   │
├───────────────────────────────────────────────────┤
│                                                   │
│  if ( is_user_logged_in() ) {                     │
│      └─► Redirect to homepage                     │
│      └─► Do NOT show registration/login forms     │
│  } else {                                         │
│      └─► Display authentication forms             │
│  }                                                │
│                                                   │
└───────────────────────────────────────────────────┘
```

---

## 5. Admin Panel Features

### 5.1 Menu Structure

| Menu Item | Slug | Functionality |
|-----------|------|---------------|
| 🔐 **AuthMe** (Main) | `authme` | Dashboard with plugin overview and quick statistics |
| └── 📊 **Database** | `authme-database` | Check and create required database tables |

### 5.2 Database Management Page

#### Status Check Logic

```
1. Check existence of wp_authme_otp_storage table
2. Verify all required columns exist within the table
3. Display status with visual indicators:
   ├─► ✅ Green checkmark for existing
   └─► ❌ Red X for missing
```

#### Create Tables Logic

| Scenario | Action | Message |
|----------|--------|---------|
| Table exists with all columns | No action needed | "Already Created" with success toast |
| Table is missing | Create table with all columns | "Table Created Successfully" |
| Columns are missing | Alter table to add columns | "Table Updated Successfully" |

### 5.3 Plugin Settings Link

On the WordPress installed plugins page (`plugins.php`), a **'Settings'** link appears in the AuthMe plugin row. This link redirects administrators directly to the AuthMe dashboard page.

---

## 6. Frontend Components

### 6.1 CSS Structure

#### 📂 CSS File Structure

| File Path | Purpose |
|-----------|---------|
| `assets/css/global.css` | Global CSS variables, color schemes, fonts, common utility classes |
| `assets/css/register.css` | Registration form specific styles |
| `assets/css/login.css` | Login form specific styles |
| `assets/css/toaster.css` | Toast notification styles with animations |

#### 🎨 Global CSS Variables

```css
:root {
    /* Colors */
    --authme-primary: #0073aa;
    --authme-secondary: #005177;
    --authme-success: #28a745;
    --authme-error: #dc3232;
    --authme-warning: #ffb900;
    
    /* Text */
    --authme-text: #333333;
    --authme-text-light: #666666;
    
    /* Layout */
    --authme-border: #dddddd;
    --authme-bg: #ffffff;
    --authme-bg-light: #f8f9fa;
    --authme-radius: 4px;
    --authme-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
```

### 6.2 JavaScript Structure

#### 📂 JavaScript File Structure

| File Path | Purpose |
|-----------|---------|
| `assets/js/global.js` | Global utilities: toaster functions, AJAX wrapper, validation helpers |
| `assets/js/register.js` | Registration form: field validation, AJAX calls, OTP timer management |
| `assets/js/login.js` | Login form: username/email detection, password validation, login OTP handling |
| `assets/js/toaster.js` | Toast notification system: show/hide, auto-dismiss, stacking management |

### 6.3 Toaster Notification System

The toaster notification system provides non-intrusive user feedback through notifications that appear in the **top-right corner** of the screen.

#### ✨ Features

| Feature | Description |
|---------|-------------|
| ⏱️ **Auto-dismiss** | Configurable timeout (default: 5000ms) |
| ❌ **Manual dismiss** | Close button for immediate dismissal |
| 📚 **Stacking support** | Multiple simultaneous toasts |
| 🎬 **Animations** | Slide-in on appear, fade-out on dismiss |
| 🎨 **Type icons** | Success ✅, Error ❌, Warning ⚠️, Info ℹ️ |

---

## 7. Security Considerations

### 7.1 Password Security

| Measure | Description |
|---------|-------------|
| 🔒 **Hashing** | Passwords hashed using WordPress's standard algorithm (bcrypt/MD5) |
| 💪 **Strength validation** | Minimum security standards enforced during registration |
| 🚫 **No plain text** | Passwords never stored in plain text format |
| 👁️ **Secure toggle** | Password visibility toggle implemented without exposing in source |

### 7.2 OTP Security

| Measure | Description |
|---------|-------------|
| ⏱️ **Limited validity** | 60-second validity period limits misuse window |
| 🔒 **Rate limiting** | OTP verification attempts can be rate-limited |
| 🔄 **Auto-invalidation** | New OTP generation invalidates previous ones |
| 🛡️ **Secure storage** | OTPs stored in database, never exposed to frontend |
| 🔐 **Two-factor auth** | Login OTP prevents unauthorized access even with compromised password |

### 7.3 Incomplete Registration Prevention

> 🛡️ **Critical Security Feature:** User accounts are created **only after successful OTP verification**. If a user initiates registration but does not complete OTP verification, no account is created. This prevents database pollution from incomplete registration attempts.

---

## 8. AJAX API Endpoints

All endpoints are protected by WordPress **nonces for CSRF protection**.

| Action Name | Method | Purpose |
|-------------|--------|---------|
| `authme_check_username` | POST | Check username availability in real-time |
| `authme_check_email` | POST | Check email availability in real-time |
| `authme_send_otp` | POST | Generate and send OTP via wp_mail() (registration/login) |
| `authme_verify_otp` | POST | Verify submitted OTP code (registration/login) |
| `authme_register_user` | POST | Complete user registration process |
| `authme_login_user` | POST | Validate login credentials |
| `authme_check_user_exists` | POST | Check user existence by username/email |

---

## 9. Template Files

| Template File | Description |
|---------------|-------------|
| `templates/register.php` | Registration form HTML with all input fields, validation messages, and OTP section |
| `templates/login.php` | Login form HTML with unified username/email field, password field, and OTP section |
| `templates/toaster.php` | Toaster notification container template |
| `templates/email-otp.php` | OTP email template with professional design (used for both registration and login) |

---

## 10. User Role Assignment

Successfully registered users are assigned the **`customer`** role through WordPress's built-in role system.

### Role Storage Format

WordPress stores roles in serialized format in the `wp_usermeta` table:

```
meta_key: wp_capabilities
meta_value: a:1:{s:8:"customer";b:1;}
```

This serialized array indicates that the user has been assigned the `customer` role.

---

## 11. Error Handling

All errors are displayed through **toaster notifications** for consistent user experience.

| Error Scenario | Error Message |
|----------------|---------------|
| Username already exists | 🔴 "Username not available" |
| Email already registered | 🔴 "Email already registered" |
| Invalid email format | 🔴 "Please enter a valid email address" |
| Username starts with number | 🔴 "Username must start with an alphabet character" |
| Password mismatch | 🔴 "Passwords do not match" |
| OTP expired | 🔴 "OTP has expired. Please request a new one." |
| Invalid OTP | 🔴 "Invalid OTP. Please try again." |
| Email send failure | 🔴 "Failed to send OTP. Please try again." |
| User not found (login) | 🔴 "User not found. Please check your credentials." |
| Incorrect password | 🔴 "Incorrect password. Please try again." |

---

## 12. Testing Requirements

### 12.1 Unit Testing

- [ ] Username validation function (valid and invalid format)
- [ ] Email validation function (format and uniqueness)
- [ ] Password strength validation
- [ ] OTP generation and expiry logic (registration and login)
- [ ] Role assignment function

### 12.2 Integration Testing

- [ ] Complete registration flow (form → OTP → verification → account creation)
- [ ] Complete login flow (credentials → password → OTP → verification → login)
- [ ] wp_mail() email delivery (registration and login OTP)
- [ ] Database table creation and verification
- [ ] Logged-in user redirect functionality

### 12.3 Security Testing

- [ ] SQL injection attempts on all input fields
- [ ] XSS attempts on form fields
- [ ] CSRF protection verification (nonce validation)
- [ ] OTP brute force resistance (registration and login)
- [ ] Session handling verification after login
- [ ] Two-factor authentication bypass attempts

---

## 13. Acceptance Criteria

### Registration

| # | Criteria |
|---|----------|
| 1 | ✅ Registration form displays correctly with all fields and validation indicators |
| 2 | ✅ Real-time username and email validation functions correctly |
| 3 | ✅ Registration OTP is successfully sent via wp_mail() |
| 4 | ✅ Registration OTP verification completes within 60-second window |
| 5 | ✅ User account created only after successful OTP verification |

### Login

| # | Criteria |
|---|----------|
| 6 | ✅ Login form correctly validates username/email and password |
| 7 | ✅ Login OTP is successfully sent to registered email via wp_mail() |
| 8 | ✅ Login OTP verification completes within 60-second window |
| 9 | ✅ User logged in only after successful login OTP verification |

### General

| # | Criteria |
|---|----------|
| 10 | ✅ Customer role correctly assigned to newly registered users |
| 11 | ✅ Logged-in users redirected to homepage |
| 12 | ✅ Admin menu and sub-menu accessible and functional |
| 13 | ✅ Database table created correctly with all required columns |
| 14 | ✅ Settings link on plugins page redirects to admin dashboard |
| 15 | ✅ Password visibility toggle functions correctly |
| 16 | ✅ Toaster notifications display correctly for all actions |
| 17 | ✅ All names use `authme` prefix consistently |

---

## 14. Document Control

| Attribute | Value |
|-----------|-------|
| **Document Version** | 1.0.0 |
| **Plugin Name** | AuthMe |
| **Author** | Art-Tech Fuzion |
| **Website** | arttechfuzion.com |
| **Creation Date** | March 19, 2026 |
| **Last Updated** | March 19, 2026 |
| **Status** | Final |

---

### 📝 Document Prepared By

**Art-Tech Fuzion**  
[arttechfuzion.com](https://arttechfuzion.com)

---

*© 2026 Art-Tech Fuzion. All rights reserved.*
