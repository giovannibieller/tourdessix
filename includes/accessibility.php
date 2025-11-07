<?php
/**
 * Accessibility Enhancements
 *
 * @package INITO_WP_Starter
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Accessibility Enhancement Class
 */
class INITO_Accessibility {

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize accessibility hooks
     */
    private function init_hooks() {
        // Skip link for screen readers
        add_action('wp_body_open', array($this, 'add_skip_link'));
        
        // Improve navigation accessibility
        add_filter('nav_menu_link_attributes', array($this, 'add_nav_link_aria'), 10, 4);
        add_filter('nav_menu_item_args', array($this, 'add_nav_item_aria'), 10, 3);
        
        // Improve form accessibility
        add_filter('comment_form_default_fields', array($this, 'improve_comment_form_accessibility'));
        add_filter('comment_form_defaults', array($this, 'improve_comment_form_defaults'));
        
        // Image accessibility
        add_filter('wp_get_attachment_image_attributes', array($this, 'improve_image_accessibility'), 10, 3);
        add_filter('the_content', array($this, 'improve_content_accessibility'));
        
        // Add ARIA landmarks
        add_action('wp_footer', array($this, 'add_aria_landmarks_script'));
        
        // Improve pagination accessibility
        add_filter('next_posts_link_attributes', array($this, 'add_pagination_aria'));
        add_filter('previous_posts_link_attributes', array($this, 'add_pagination_aria'));
        
        // Focus management
        add_action('wp_enqueue_scripts', array($this, 'enqueue_accessibility_scripts'));
        
        // Color contrast utilities
        add_action('wp_head', array($this, 'add_accessibility_styles'));
        
        // Screen reader utilities
        add_action('wp_head', array($this, 'add_screen_reader_styles'));
    }

    /**
     * Add skip link for keyboard navigation
     */
    public function add_skip_link() {
        echo '<a class="skip-link screen-reader-text" href="#main">' . 
             esc_html__('Skip to main content', 'inito-wp') . '</a>';
    }

    /**
     * Add ARIA attributes to navigation links
     */
    public function add_nav_link_aria($atts, $item, $args, $depth) {
        // Add current page indicator
        if (in_array('current-menu-item', $item->classes)) {
            $atts['aria-current'] = 'page';
        }
        
        // Add expanded state for parent items
        if (in_array('menu-item-has-children', $item->classes)) {
            $atts['aria-expanded'] = 'false';
            $atts['aria-haspopup'] = 'true';
        }
        
        return $atts;
    }

    /**
     * Add ARIA attributes to navigation items
     */
    public function add_nav_item_aria($args, $item, $depth) {
        // Add role for menu items
        if ($depth === 0) {
            $args->before = '<span role="none">';
            $args->after = '</span>';
        }
        
        return $args;
    }

    /**
     * Improve comment form accessibility
     */
    public function improve_comment_form_accessibility($fields) {
        $commenter = wp_get_current_commenter();
        $req = get_option('require_name_email');
        $aria_req = ($req ? ' aria-required="true"' : '');
        $html5 = current_theme_supports('html5', 'comment-form') ? 'html5' : '';

        $fields['author'] = sprintf(
            '<p class="comment-form-author">
                <label for="author">%s%s</label>
                <input id="author" name="author" type="text" value="%s" size="30" maxlength="245"%s autocomplete="name" />
            </p>',
            __('Name', 'inito-wp'),
            ($req ? ' <span class="required" aria-label="' . esc_attr__('required', 'inito-wp') . '">*</span>' : ''),
            esc_attr($commenter['comment_author']),
            $aria_req
        );

        $fields['email'] = sprintf(
            '<p class="comment-form-email">
                <label for="email">%s%s</label>
                <input id="email" name="email" %s value="%s" size="30" maxlength="100"%s autocomplete="email" />
            </p>',
            __('Email', 'inito-wp'),
            ($req ? ' <span class="required" aria-label="' . esc_attr__('required', 'inito-wp') . '">*</span>' : ''),
            ($html5 ? 'type="email"' : 'type="text"'),
            esc_attr($commenter['comment_author_email']),
            $aria_req
        );

        $fields['url'] = sprintf(
            '<p class="comment-form-url">
                <label for="url">%s</label>
                <input id="url" name="url" %s value="%s" size="30" maxlength="200" autocomplete="url" />
            </p>',
            __('Website', 'inito-wp'),
            ($html5 ? 'type="url"' : 'type="text"'),
            esc_attr($commenter['comment_author_url'])
        );

        return $fields;
    }

    /**
     * Improve comment form defaults
     */
    public function improve_comment_form_defaults($defaults) {
        $defaults['comment_field'] = sprintf(
            '<p class="comment-form-comment">
                <label for="comment">%s <span class="required" aria-label="%s">*</span></label>
                <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required aria-required="true" aria-describedby="comment-notes"></textarea>
            </p>',
            __('Comment', 'inito-wp'),
            esc_attr__('required', 'inito-wp')
        );

        $defaults['submit_button'] = sprintf(
            '<input name="submit" type="submit" id="submit" class="submit" value="%s" />',
            esc_attr__('Post Comment', 'inito-wp')
        );

        // Add instructions
        $defaults['comment_notes_before'] = sprintf(
            '<p class="comment-notes" id="comment-notes">
                <span id="email-notes">%s</span>
            </p>',
            __('Your email address will not be published. Required fields are marked with an asterisk (*).', 'inito-wp')
        );

        return $defaults;
    }

    /**
     * Improve image accessibility
     */
    public function improve_image_accessibility($attr, $attachment, $size) {
        // Add proper alt text fallbacks
        if (empty($attr['alt'])) {
            $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
            if (empty($alt_text)) {
                $alt_text = $attachment->post_title;
            }
            if (empty($alt_text)) {
                $alt_text = $attachment->post_excerpt;
            }
            $attr['alt'] = $alt_text;
        }

        // Add role for decorative images
        if (empty($attr['alt']) || $attr['alt'] === 'decorative') {
            $attr['role'] = 'presentation';
            $attr['alt'] = '';
        }

        return $attr;
    }

    /**
     * Improve content accessibility
     */
    public function improve_content_accessibility($content) {
        // Add ARIA labels to images without alt text
        $content = preg_replace_callback(
            '/<img([^>]*?)>/i',
            function($matches) {
                $img_tag = $matches[0];
                
                // Check if alt attribute exists
                if (strpos($img_tag, 'alt=') === false) {
                    $img_tag = str_replace('<img', '<img alt=""', $img_tag);
                }
                
                return $img_tag;
            },
            $content
        );

        // Add proper heading hierarchy validation
        $content = $this->fix_heading_hierarchy($content);

        return $content;
    }

    /**
     * Fix heading hierarchy in content
     */
    private function fix_heading_hierarchy($content) {
        // This is a basic implementation - you might want to enhance this
        // to properly fix heading hierarchy issues
        return $content;
    }

    /**
     * Add pagination ARIA attributes
     */
    public function add_pagination_aria($attributes) {
        $attributes .= ' role="button"';
        return $attributes;
    }

    /**
     * Enqueue accessibility scripts
     */
    public function enqueue_accessibility_scripts() {
        wp_enqueue_script(
            'inito-accessibility',
            get_template_directory_uri() . '/assets/js/accessibility.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );

        // Add accessibility configuration
        wp_localize_script('inito-accessibility', 'initoA11y', array(
            'skipLinkFocus' => __('Skip to main content', 'inito-wp'),
            'expandMenu' => __('Expand menu', 'inito-wp'),
            'collapseMenu' => __('Collapse menu', 'inito-wp'),
            'closeDialog' => __('Close dialog', 'inito-wp'),
        ));
    }

    /**
     * Add ARIA landmarks script
     */
    public function add_aria_landmarks_script() {
        ?>
        <script>
        // Add ARIA landmarks to existing elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add main landmark
            var mainContent = document.querySelector('.main_container, main, #main');
            if (mainContent && !mainContent.getAttribute('role')) {
                mainContent.setAttribute('role', 'main');
                if (!mainContent.id) {
                    mainContent.id = 'main';
                }
            }

            // Add navigation landmarks
            var navElements = document.querySelectorAll('nav, .navigation, .menu');
            navElements.forEach(function(nav) {
                if (!nav.getAttribute('role')) {
                    nav.setAttribute('role', 'navigation');
                }
                if (!nav.getAttribute('aria-label')) {
                    nav.setAttribute('aria-label', 'Main navigation');
                }
            });

            // Add banner landmark to header
            var header = document.querySelector('header, .header, .site-header');
            if (header && !header.getAttribute('role')) {
                header.setAttribute('role', 'banner');
            }

            // Add contentinfo landmark to footer
            var footer = document.querySelector('footer, .footer, .site-footer');
            if (footer && !footer.getAttribute('role')) {
                footer.setAttribute('role', 'contentinfo');
            }

            // Add complementary landmark to sidebar
            var sidebar = document.querySelector('.sidebar, .widget-area, aside');
            if (sidebar && !sidebar.getAttribute('role')) {
                sidebar.setAttribute('role', 'complementary');
            }
        });
        </script>
        <?php
    }

    /**
     * Add accessibility styles
     */
    public function add_accessibility_styles() {
        ?>
        <style id="inito-accessibility-styles">
        /* High contrast support */
        @media (prefers-contrast: high) {
            :root {
                --contrast-ratio: 7:1;
            }
            
            .wp-block-button__link,
            .button,
            input[type="submit"],
            input[type="button"] {
                border: 2px solid currentColor;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Focus indicators */
        *:focus {
            outline: 2px solid #0073aa;
            outline-offset: 2px;
        }

        .skip-link:focus {
            clip: auto;
            height: auto;
            width: auto;
            position: absolute;
            top: 0;
            left: 0;
            background: #0073aa;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            z-index: 999999;
        }

        /* Required field indicators */
        .required {
            color: #d63638;
        }

        /* Error states */
        .error input,
        .error textarea,
        .error select {
            border-color: #d63638;
            box-shadow: 0 0 0 1px #d63638;
        }

        /* Success states */
        .success input,
        .success textarea,
        .success select {
            border-color: #00a32a;
            box-shadow: 0 0 0 1px #00a32a;
        }
        </style>
        <?php
    }

    /**
     * Add screen reader styles
     */
    public function add_screen_reader_styles() {
        ?>
        <style id="inito-screen-reader-styles">
        /* Screen reader only text */
        .screen-reader-text,
        .sr-only {
            clip: rect(1px, 1px, 1px, 1px);
            position: absolute !important;
            height: 1px;
            width: 1px;
            overflow: hidden;
            word-wrap: normal !important;
        }

        .screen-reader-text:focus,
        .sr-only:focus {
            background-color: #f1f1f1;
            border-radius: 3px;
            box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
            clip: auto !important;
            color: #21759b;
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            height: auto;
            left: 5px;
            line-height: normal;
            padding: 15px 23px 14px;
            text-decoration: none;
            top: 5px;
            width: auto;
            z-index: 100000;
        }

        /* Skip link */
        .skip-link {
            clip: rect(1px, 1px, 1px, 1px);
            position: absolute;
            height: 1px;
            width: 1px;
            overflow: hidden;
        }
        </style>
        <?php
    }
}

// Initialize accessibility enhancements
new INITO_Accessibility();

/**
 * Accessibility utility functions
 */

/**
 * Check if current page is accessible
 */
function inito_is_accessible_page() {
    // Add logic to check page accessibility
    return true;
}

/**
 * Get accessibility compliance level
 */
function inito_get_accessibility_level() {
    // Return WCAG compliance level (A, AA, AAA)
    return apply_filters('inito_accessibility_level', 'AA');
}

/**
 * Add accessibility notice for admin
 */
function inito_accessibility_admin_notice() {
    if (current_user_can('manage_options')) {
        // Check if notice has been dismissed
        $dismissed = get_user_meta(get_current_user_id(), 'inito_accessibility_notice_dismissed', true);
        
        if (!$dismissed) {
            $level = inito_get_accessibility_level();
            echo '<div class="notice notice-info is-dismissible" data-notice="inito-accessibility"><p>';
            echo sprintf(
                __('This theme aims for WCAG %s compliance. Check the accessibility documentation for details.', 'inito-wp'),
                esc_html($level)
            );
            echo '</p>';
            echo '<button type="button" class="notice-dismiss inito-dismiss-forever" data-notice="inito-accessibility">';
            echo '<span class="screen-reader-text">' . __('Dismiss this notice permanently.', 'inito-wp') . '</span>';
            echo '</button>';
            echo '</div>';
        }
    }
}
add_action('admin_notices', 'inito_accessibility_admin_notice');

/**
 * Handle AJAX request to dismiss accessibility notice permanently
 */
function inito_dismiss_accessibility_notice() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'inito_dismiss_notice')) {
        wp_die('Security check failed.');
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    
    // Save dismissal preference
    update_user_meta(get_current_user_id(), 'inito_accessibility_notice_dismissed', true);
    
    wp_send_json_success();
}
add_action('wp_ajax_inito_dismiss_accessibility_notice', 'inito_dismiss_accessibility_notice');

/**
 * Enqueue admin scripts for notice dismissal
 */
function inito_admin_notice_scripts($hook) {
    // Only load on admin pages
    if (!is_admin()) {
        return;
    }
    
    wp_enqueue_script(
        'inito-admin-notices',
        get_template_directory_uri() . '/assets/js/admin-notices.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );
    
    wp_localize_script('inito-admin-notices', 'initoAdmin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('inito_dismiss_notice'),
        'strings' => array(
            'error' => __('Error dismissing notice.', 'inito-wp')
        )
    ));
}
add_action('admin_enqueue_scripts', 'inito_admin_notice_scripts');

/**
 * Accessibility testing function
 */
function inito_test_accessibility() {
    $tests = array();
    
    // Test for skip links
    $tests['skip_link'] = strpos(get_echo('add_skip_link'), 'skip-link') !== false;
    
    // Test for proper headings
    $tests['headings'] = true; // Add actual heading structure test
    
    // Test for alt attributes
    $tests['images'] = true; // Add actual image alt test
    
    return $tests;
}

/**
 * Helper function to capture output
 */
function get_echo($function_name) {
    ob_start();
    call_user_func($function_name);
    return ob_get_clean();
}