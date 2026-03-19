/**
 * AuthMe — Global JavaScript Utilities
 *
 * Provides a shared AJAX wrapper and validation helpers
 * used across all AuthMe frontend scripts.
 *
 * @package AuthMe
 */

/* global authme_ajax */

(function (window) {
    'use strict';

    /**
     * Central AJAX helper using Fetch API.
     *
     * @param {string}   action   WordPress AJAX action name.
     * @param {object}   data     Key-value pairs to send.
     * @param {function} onSuccess Callback on success.
     * @param {function} onError   Callback on error.
     */
    function authmeAjax(action, data, onSuccess, onError) {
        var formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', authme_ajax.nonce);

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                formData.append(key, data[key]);
            }
        }

        fetch(authme_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            if (result.success) {
                if (typeof onSuccess === 'function') onSuccess(result.data);
            } else {
                if (typeof onError === 'function') onError(result.data);
            }
        })
        .catch(function (err) {
            if (typeof onError === 'function') {
                onError({ message: 'Network error. Please try again.' });
            }
        });
    }

    /**
     * Validate email format using regex.
     *
     * @param {string} email
     * @returns {boolean}
     */
    function authmeIsValidEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Check password strength.
     * Returns: 'weak' | 'fair' | 'good' | 'strong'
     *
     * @param {string} password
     * @returns {string}
     */
    function authmePasswordStrength(password) {
        var score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;

        if (score <= 1) return 'weak';
        if (score <= 2) return 'fair';
        if (score <= 3) return 'good';
        return 'strong';
    }

    /**
     * Check if a password meets the minimum requirements.
     * Min 8 chars, 1 upper, 1 lower, 1 number, 1 special char.
     *
     * @param {string} password
     * @returns {object} { valid: bool, message: string }
     */
    function authmeValidatePassword(password) {
        if (password.length < 8) {
            return { valid: false, message: 'Minimum 8 characters required.' };
        }
        if (!/[a-z]/.test(password)) {
            return { valid: false, message: 'At least 1 lowercase letter required.' };
        }
        if (!/[A-Z]/.test(password)) {
            return { valid: false, message: 'At least 1 uppercase letter required.' };
        }
        if (!/[0-9]/.test(password)) {
            return { valid: false, message: 'At least 1 number required.' };
        }
        if (!/[^a-zA-Z0-9]/.test(password)) {
            return { valid: false, message: 'At least 1 special character required.' };
        }
        return { valid: true, message: 'Password is strong.' };
    }

    /**
     * Set a field validation message and style.
     *
     * @param {HTMLElement} input   The input element.
     * @param {HTMLElement} msgEl   The message span element.
     * @param {string}      type    'success' | 'error' | '' (clear).
     * @param {string}      message Display text.
     */
    function authmeSetFieldState(input, msgEl, type, message) {
        // Remove previous state classes
        input.classList.remove('authme-input-success', 'authme-input-error');
        if (msgEl) {
            msgEl.classList.remove('authme-msg-success', 'authme-msg-error');
            msgEl.textContent = '';
        }

        if (type === 'success') {
            input.classList.add('authme-input-success');
            if (msgEl) {
                msgEl.classList.add('authme-msg-success');
                msgEl.textContent = message;
            }
        } else if (type === 'error') {
            input.classList.add('authme-input-error');
            if (msgEl) {
                msgEl.classList.add('authme-msg-error');
                msgEl.textContent = message;
            }
        }
    }

    // Expose globally
    window.authmeAjax           = authmeAjax;
    window.authmeIsValidEmail   = authmeIsValidEmail;
    window.authmePasswordStrength = authmePasswordStrength;
    window.authmeValidatePassword = authmeValidatePassword;
    window.authmeSetFieldState  = authmeSetFieldState;

})(window);
