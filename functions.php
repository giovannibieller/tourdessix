<?php
    // Include security enhancements
    require_once get_template_directory() . '/includes/security.php';
    
    // Include performance optimizations
    require_once get_template_directory() . '/includes/performance.php';
    
    // Include performance monitoring (only in debug mode)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        require_once get_template_directory() . '/includes/performance-monitor.php';
    }
    
    // Include accessibility enhancements
    require_once get_template_directory() . '/includes/accessibility.php';
    
    // Remove admin bar for non-admin users
    add_action('after_setup_theme', 'remove_admin_bar');
    
    function remove_admin_bar() {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    // Theme setup
    function theme_setup() {
        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        // Let WordPress manage the document title.
        add_theme_support( 'title-tag' );

        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support( 'post-thumbnails' );

        // Switch default core markup for search form, comment form, and comments to output valid HTML5.
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ) );

        // Add theme support for selective refresh for widgets.
        add_theme_support( 'customize-selective-refresh-widgets' );

        // Add support for responsive embedded content.
        add_theme_support( 'responsive-embeds' );

        // Add support for wide alignment.
        add_theme_support( 'align-wide' );

        // Add support for block styles.
        add_theme_support( 'wp-block-styles' );

        // Add support for editor styles.
        add_theme_support( 'editor-styles' );
        add_editor_style( 'assets/css/editor-styles.css' );

        // Add menu support
        add_theme_support( 'menus' );
        
        // Add custom logo support
        add_theme_support( 'custom-logo', array(
            'height'      => 100,
            'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
        ) );
        
        // Define custom image sizes
        add_image_size( 'hero-image', 1200, 600, true );
        add_image_size( 'card-thumb', 400, 300, true );
        add_image_size( 'post-thumb', 800, 450, true );
        
        // Register navigation menus
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'inito-wp' ),
            'footer'  => __( 'Footer Menu', 'inito-wp' ),
        ) );
    }
    add_action( 'after_setup_theme', 'theme_setup' );

    // Add body classes
    function body_classes()
    {
        $catClass = '';
        if (is_home()) {
            $catClass = 'home';
        } elseif (is_category()) {
            $currentCat = get_query_var('cat');
            $catClass = get_cat_name($currentCat);
        } elseif (is_tax()) {
            $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
            $catClass = $term->slug;
        } elseif (is_page()) {
            $slug = basename(get_permalink());
            $catClass = 'page page-' . str_replace(' ', '_', strtolower(get_the_title()));
        } elseif (is_single()) {
            $slug = basename(get_permalink());
            $catClass = 'single ' . $slug;
        } elseif (is_404()) {
            $catClass = 'error-page';
        }

        echo $catClass;
    }

    // Add current item class
    function add_current_nav_class($classes, $item) {
    
        // Getting the current post details
        global $post;
        
        // Getting the post type of the current post
        $current_post_type = get_post_type_object(get_post_type($post->ID));
        $current_post_type_slug = $current_post_type->rewrite[slug];
            
        // Getting the URL of the menu item
        $menu_slug = strtolower(trim($item->url));
        
        // If the menu item URL contains the current post types slug add the current-menu-item class
        if ( strpos($menu_slug,$current_post_type_slug) !== false ) {
            $classes[] = 'current-menu-item';
        }
        
        // Return the corrected set of classes to be added to the menu item
        return $classes;
    
    }

    // Include custom post types in search results
    function include_custom_post_types( $query ) {
        $custom_post_type = get_query_var( 'post_type' );
    
        if ( is_archive() ) {
            if ( empty( $custom_post_type ) ) $query->set( 'post_type' , get_post_types() );
        }
    
        if ( is_search() ) {
            if ( empty( $custom_post_type ) ) {
                $query->set( 'post_type' , array('post'));
            }
        }
    
        return $query;
    }
    add_filter( 'pre_get_posts' , 'include_custom_post_types' );

    // Allow SVG uploads
    function cc_mime_types($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
    add_filter('upload_mimes', 'cc_mime_types');

    // Move Yoast SEO to bottom
    function yoasttobottom() { return 'low'; }
    add_filter( 'wpseo_metabox_prio', 'yoasttobottom');

    // Load Translations
    function wp_inito_load_theme_textdomain() {
        load_theme_textdomain( 'tour-des-six', get_template_directory() . '/lang' );
    }
    add_action( 'after_setup_theme', 'wp_inito_load_theme_textdomain' );

    // Register tr() function
    function tr($text){
        return _e($text, 'tour-des-six');
    }

    // Register ACF options pages
    function register_acf_options_pages() {
        if( function_exists('acf_add_options_page') ) {
            acf_add_options_page(array(
                'page_title'    => 'SEO Settings',
                'menu_title'    => 'SEO Settings',
                'menu_slug'     => 'seo-settings',
                'post_id'     => 'seo-settings',
            ));

            acf_add_options_page(array(
                'page_title'    => 'Utils',
                'menu_title'    => 'Utils',
                'menu_slug'     => 'utils',
                'post_id'     => 'utils',
            ));
        }
    }
    add_action('acf/init', 'register_acf_options_pages');

    /**
     * Return the post excerpt, if one is set, else generate it using the
     * post content. If original text exceeds $num_of_words, the text is
     * trimmed and an ellipsis (…) is added to the end.
     *
     * @param  int|string|WP_Post $post_id   Post ID or object. Default is current post.
     * @param  int                $num_words Number of words. Default is 33.
     * @return string                        The generated excerpt.
     */
    function get_post_excerpt( $post_id = null, $num_words = 33 ) {

        $post = $post_id ? get_post( $post_id ) : get_post( get_the_ID() );
        $text = get_the_excerpt( $post );

        if ( ! $text ) {
            $text = get_post_field( 'post_content', $post );
        }

        $generated_excerpt = wp_trim_words( $text, $num_words );

        return apply_filters( 'get_the_excerpt', $generated_excerpt, $post );
    }

    // Remove Wordpress version from Source Code
    function remove_version_info() { return ''; }
    add_filter('the_generator', 'remove_version_info');

    // Enhanced Security improvements
    function theme_security_headers() {
        // Remove WordPress version from RSS feeds
        add_filter('the_generator', '__return_empty_string');
        
        // Hide WordPress version from scripts and styles
        function remove_version_scripts_styles($src) {
            if (strpos($src, 'ver=')) {
                $src = remove_query_arg('ver', $src);
            }
            return $src;
        }
        add_filter('style_loader_src', 'remove_version_scripts_styles', 9999);
        add_filter('script_loader_src', 'remove_version_scripts_styles', 9999);
        
        // Add security headers
        if (!is_admin()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            // Content Security Policy
            $csp = "default-src 'self'; ";
            $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.googletagmanager.com *.google-analytics.com; ";
            $csp .= "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com; ";
            $csp .= "font-src 'self' *.googleapis.com *.gstatic.com data:; ";
            $csp .= "img-src 'self' data: *.gravatar.com *.w.org *.google-analytics.com *.googletagmanager.com; ";
            $csp .= "connect-src 'self' *.google-analytics.com *.analytics.google.com *.googletagmanager.com; ";
            $csp .= "frame-src 'self' *.youtube.com *.vimeo.com *.google.com; ";
            $csp .= "object-src 'none'; ";
            $csp .= "base-uri 'self';";
            
            header('Content-Security-Policy: ' . $csp);
        }
    }
    add_action('init', 'theme_security_headers');

    // Disable file editing in WordPress admin
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }

    // Additional security measures
    function theme_additional_security() {
        // Remove WordPress version from head
        remove_action('wp_head', 'wp_generator');
        
        // Remove version from RSS
        add_filter('the_generator', '__return_empty_string');
        
        // Disable XML-RPC (if not needed)
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Remove really simple discovery link
        remove_action('wp_head', 'rsd_link');
        
        // Remove wlwmanifest.xml (Windows Live Writer)
        remove_action('wp_head', 'wlwmanifest_link');
        
        // Remove the REST API endpoint
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        
        // Disable pingbacks
        function disable_pingback(&$links) {
            foreach($links as $l => $link)
                if(0 === strpos($link, get_option('home')))
                    unset($links[$l]);
        }
        add_action('pre_ping', 'disable_pingback');
        
        // Disable self pingbacks
        function no_self_ping(&$links) {
            $home = get_option('home');
            foreach($links as $l => $link)
                if(0 === strpos($link, $home))
                    unset($links[$l]);
        }
        add_action('pre_ping', 'no_self_ping');
        
        // Remove query strings from static resources
        function remove_script_version($src){
            $parts = explode('?ver', $src);
            return $parts[0];
        }
        add_filter('script_loader_src', 'remove_script_version', 15, 1);
        add_filter('style_loader_src', 'remove_script_version', 15, 1);
        
        // Hide login errors
        function hide_login_errors(){
            return 'Something is wrong!';
        }
        add_filter('login_errors', 'hide_login_errors');
        
        // Remove author pages to prevent username enumeration
        function disable_author_pages() {
            global $wp_query;
            if (is_author()) {
                $wp_query->set_404();
                status_header(404);
            }
        }
        add_action('template_redirect', 'disable_author_pages');
        
        // Disable user enumeration
        function disable_user_enumeration($redirect, $request) {
            if (preg_match('/\?author=([0-9]*)/i', $request)) {
                wp_redirect(home_url(), 301);
                exit;
            }
        }
        add_filter('redirect_canonical', 'disable_user_enumeration', 10, 2);
    }
    add_action('init', 'theme_additional_security');
    
    // Secure file uploads
    function secure_file_uploads($mimes) {
        // Remove potentially dangerous file types
        unset($mimes['exe']);
        unset($mimes['com']);
        unset($mimes['bat']);
        unset($mimes['pif']);
        unset($mimes['scr']);
        unset($mimes['vbs']);
        unset($mimes['lnk']);
        
        return $mimes;
    }
    add_filter('upload_mimes', 'secure_file_uploads');
    
    // Limit login attempts (basic implementation)
    function limit_login_attempts() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = get_transient('login_attempts_' . $ip);
        
        if ($attempts && $attempts >= 5) {
            wp_die('Too many login attempts. Please try again later.');
        }
    }
    add_action('wp_login_failed', function() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = get_transient('login_attempts_' . $ip);
        $attempts = $attempts ? $attempts + 1 : 1;
        set_transient('login_attempts_' . $ip, $attempts, 15 * MINUTE_IN_SECONDS);
    });
    add_action('wp_login', function() {
        $ip = $_SERVER['REMOTE_ADDR'];
        delete_transient('login_attempts_' . $ip);
    });
    add_action('login_form', 'limit_login_attempts');

    // Performance optimizations
    function theme_performance_optimizations() {
        // Remove emoji scripts and styles
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        
        // Remove unnecessary WordPress features
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        
        // Remove feed links (unless you need them)
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }
    add_action('init', 'theme_performance_optimizations');

    // Defer parsing of JavaScript
    function defer_parsing_of_js($url) {
        if (is_admin()) return $url; // Don't defer in admin
        if (FALSE === strpos($url, '.js')) return $url; // Not a JS file
        if (strpos($url, 'jquery.js')) return $url; // Don't defer jQuery
        return str_replace(' src', ' defer src', $url);
    }
    add_filter('script_loader_tag', 'defer_parsing_of_js', 10);

    // Proper asset enqueueing
    function theme_enqueue_assets() {
        $theme_version = wp_get_theme()->get('Version');

        // Enqueue theme styles
        wp_enqueue_style( 
            'theme-main-style', 
            get_template_directory_uri() . '/dist/css/main.css', 
            array(), 
            $theme_version
        );
        
        // Enqueue theme scripts
        wp_enqueue_script( 
            'theme-main-script', 
            get_template_directory_uri() . '/dist/js/main.min.js', 
            array(), 
            $theme_version, 
            true // Load in footer
        );
    }
    add_action( 'wp_enqueue_scripts', 'theme_enqueue_assets' );

    // Add editor styles
    function theme_add_editor_styles() {
        add_theme_support( 'editor-styles' );
        add_editor_style( 'dist/css/editor-style.css' );
    }
    add_action( 'after_setup_theme', 'theme_add_editor_styles' );

    // Register widget areas
    function theme_widgets_init() {
        register_sidebar(array(
            'name'          => __('Primary Sidebar', 'tour-des-six'),
            'id'            => 'sidebar-1',
            'description'   => __('Add widgets here to appear in your sidebar.', 'tour-des-six'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ));

        register_sidebar(array(
            'name'          => __('Footer Widget Area', 'tour-des-six'),
            'id'            => 'footer-1',
            'description'   => __('Add widgets here to appear in your footer.', 'tour-des-six'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ));
    }
    add_action( 'widgets_init', 'theme_widgets_init' );