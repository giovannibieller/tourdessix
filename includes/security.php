<?php
/**
 * Security Configuration and Enhancements
 * 
 * @package inito-wp-theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Security Headers
 */
function theme_advanced_security_headers() {
    if (!is_admin()) {
        // Strict Transport Security (HTTPS)
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Clickjacking protection
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy (formerly Feature Policy)
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'speaker=()',
            'vibrate=()',
            'fullscreen=(self)',
            'sync-xhr=()'
        ];
        header('Permissions-Policy: ' . implode(', ', $permissions));
        
        // Expect-CT (Certificate Transparency)
        if (is_ssl()) {
            header('Expect-CT: max-age=86400, enforce');
        }
    }
}

/**
 * Content Security Policy
 */
function theme_content_security_policy() {
    if (!is_admin()) {
        $site_url = parse_url(home_url(), PHP_URL_HOST);
        
        $csp_directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.googletagmanager.com *.google-analytics.com *.gstatic.com",
            "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com",
            "font-src 'self' *.googleapis.com *.gstatic.com data:",
            "img-src 'self' data: *.gravatar.com *.w.org *.google-analytics.com *.googletagmanager.com {$site_url}",
            "connect-src 'self' *.google-analytics.com *.analytics.google.com *.googletagmanager.com",
            "frame-src 'self' *.youtube.com *.youtube-nocookie.com *.vimeo.com *.google.com",
            "media-src 'self' *.youtube.com *.vimeo.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "upgrade-insecure-requests"
        ];
        
        $csp = implode('; ', $csp_directives);
        header('Content-Security-Policy: ' . $csp);
    }
}

/**
 * Sanitize file uploads
 */
function theme_sanitize_file_uploads($file) {
    // Check file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'zip'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $file['error'] = 'File type not allowed.';
        return $file;
    }
    
    // Additional MIME type checking
    $allowed_mimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf', 'application/msword', 'application/zip'
    ];
    
    if (!in_array($file['type'], $allowed_mimes)) {
        $file['error'] = 'Invalid file type.';
        return $file;
    }
    
    // Sanitize filename
    $file['name'] = sanitize_file_name($file['name']);
    
    return $file;
}

/**
 * Database Security
 */
function theme_database_security() {
    // Disable database error reporting in production
    if (!WP_DEBUG) {
        global $wpdb;
        $wpdb->hide_errors();
    }
}

/**
 * Session Security
 */
function theme_session_security() {
    // Secure session configuration
    if (!is_admin()) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', is_ssl() ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
    }
}

/**
 * Anti-spam and bot protection
 */
function theme_anti_spam() {
    // Add honeypot field to comment forms
    function add_honeypot_to_comment_form($fields) {
        $honeypot = '<p style="display:none !important;"><label>Leave this field empty: ';
        $honeypot .= '<input type="text" name="url2" value="" size="22" tabindex="-1" autocomplete="off" /></label></p>';
        $fields['url2'] = $honeypot;
        return $fields;
    }
    add_filter('comment_form_default_fields', 'add_honeypot_to_comment_form');
    
    // Check honeypot on comment submission
    function check_honeypot_comment($commentdata) {
        if (!empty($_POST['url2'])) {
            wp_die('Spam detected.');
        }
        return $commentdata;
    }
    add_filter('preprocess_comment', 'check_honeypot_comment');
}

/**
 * Security logging
 */
function theme_security_logging($action, $details = '') {
    if (WP_DEBUG_LOG) {
        error_log(sprintf(
            '[SECURITY] %s - Action: %s, Details: %s, IP: %s, User Agent: %s',
            current_time('mysql'),
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ));
    }
}

// Initialize security measures
add_action('init', 'theme_advanced_security_headers', 1);
add_action('init', 'theme_content_security_policy', 1);
add_action('init', 'theme_database_security', 1);
add_action('init', 'theme_session_security', 1);
add_action('init', 'theme_anti_spam', 1);
add_filter('wp_handle_upload_prefilter', 'theme_sanitize_file_uploads');

// Log suspicious activities
add_action('wp_login_failed', function($username) {
    theme_security_logging('LOGIN_FAILED', "Username: {$username}");
});

add_action('wp_login', function($user_login, $user) {
    theme_security_logging('LOGIN_SUCCESS', "User: {$user_login}");
}, 10, 2);
?>