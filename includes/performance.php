<?php
/**
 * Performance Optimizations
 *
 * @package INITO_WP_Starter
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Optimization Class
 */
class INITO_Performance {

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize performance hooks
     */
    private function init_hooks() {
        // Image optimizations
        add_action('wp_enqueue_scripts', array($this, 'enqueue_lazy_loading'));
        add_filter('wp_get_attachment_image_attributes', array($this, 'add_lazy_loading_attributes'), 10, 3);
        add_filter('the_content', array($this, 'add_lazy_loading_to_content'));
        add_action('wp_head', array($this, 'add_preload_critical_images'), 1);

        // CSS optimizations
        add_action('wp_head', array($this, 'inline_critical_css'), 2);
        add_action('wp_footer', array($this, 'load_non_critical_css'));
        add_filter('style_loader_tag', array($this, 'optimize_css_delivery'), 10, 2);

        // JavaScript optimizations
        add_filter('script_loader_tag', array($this, 'optimize_js_delivery'), 10, 2);
        add_action('wp_footer', array($this, 'add_resource_hints'));

        // Resource optimizations
        add_action('wp_head', array($this, 'add_dns_prefetch'), 1);
        add_action('wp_head', array($this, 'add_preconnect'), 1);
        add_filter('wp_resource_hints', array($this, 'add_resource_hints_filter'), 10, 2);

        // Database optimizations
        add_action('init', array($this, 'optimize_database_queries'));
        add_filter('posts_clauses', array($this, 'optimize_post_queries'), 10, 2);

        // Core Web Vitals
        add_action('wp_head', array($this, 'optimize_web_vitals'));
        add_filter('render_block', array($this, 'optimize_block_rendering'), 10, 2);

        // Caching
        add_action('template_redirect', array($this, 'setup_object_cache'));
        add_filter('wp_cache_get', array($this, 'optimize_cache_usage'));
    }

    /**
     * Enqueue lazy loading script
     */
    public function enqueue_lazy_loading() {
        if (!is_admin()) {
            wp_enqueue_script(
                'inito-lazy-loading',
                get_template_directory_uri() . '/assets/js/lazy-loading.js',
                array(),
                wp_get_theme()->get('Version'),
                true
            );

            // Add lazy loading configuration
            wp_localize_script('inito-lazy-loading', 'initoLazyConfig', array(
                'rootMargin' => '50px',
                'threshold' => 0.1,
                'enableWebP' => $this->webp_supported(),
            ));
        }
    }

    /**
     * Add lazy loading attributes to images
     */
    public function add_lazy_loading_attributes($attr, $attachment, $size) {
        // Skip if in admin or RSS feed
        if (is_admin() || is_feed()) {
            return $attr;
        }

        // Add loading attribute for modern browsers
        $attr['loading'] = 'lazy';

        // Add data attributes for fallback lazy loading
        if (isset($attr['src'])) {
            $attr['data-src'] = $attr['src'];
            $attr['src'] = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
        }

        if (isset($attr['srcset'])) {
            $attr['data-srcset'] = $attr['srcset'];
            unset($attr['srcset']);
        }

        $attr['class'] = (isset($attr['class']) ? $attr['class'] . ' ' : '') . 'lazy-image';

        return $attr;
    }

    /**
     * Add lazy loading to content images
     */
    public function add_lazy_loading_to_content($content) {
        if (is_admin() || is_feed()) {
            return $content;
        }

        // Add lazy loading to images in content
        $content = preg_replace_callback(
            '/<img([^>]+)>/i',
            function($matches) {
                $img_tag = $matches[0];
                
                // Skip if already has loading attribute
                if (strpos($img_tag, 'loading=') !== false) {
                    return $img_tag;
                }

                // Add loading="lazy"
                $img_tag = str_replace('<img', '<img loading="lazy"', $img_tag);
                
                return $img_tag;
            },
            $content
        );

        // Add lazy loading to iframes
        $content = preg_replace_callback(
            '/<iframe([^>]+)>/i',
            function($matches) {
                $iframe_tag = $matches[0];
                
                // Skip if already has loading attribute
                if (strpos($iframe_tag, 'loading=') !== false) {
                    return $iframe_tag;
                }

                // Add loading="lazy"
                $iframe_tag = str_replace('<iframe', '<iframe loading="lazy"', $iframe_tag);
                
                return $iframe_tag;
            },
            $content
        );

        return $content;
    }

    /**
     * Preload critical images
     */
    public function add_preload_critical_images() {
        // Preload logo and hero images
        $logo_url = get_theme_mod('custom_logo');
        if ($logo_url) {
            $logo_src = wp_get_attachment_image_src($logo_url, 'full');
            if ($logo_src) {
                echo '<link rel="preload" as="image" href="' . esc_url($logo_src[0]) . '">' . "\n";
            }
        }

        // Preload hero image if on front page
        if (is_front_page()) {
            $hero_image = get_theme_mod('hero_background_image');
            if ($hero_image) {
                echo '<link rel="preload" as="image" href="' . esc_url($hero_image) . '">' . "\n";
            }
        }
    }

    /**
     * Inline critical CSS
     */
    public function inline_critical_css() {
        $critical_css = $this->get_critical_css();
        if ($critical_css) {
            echo '<style id="inito-critical-css">' . $critical_css . '</style>' . "\n";
        }
    }

    /**
     * Load non-critical CSS asynchronously
     */
    public function load_non_critical_css() {
        ?>
        <script>
        (function() {
            var cssFiles = [
                '<?php echo get_template_directory_uri(); ?>/style.css?v=<?php echo wp_get_theme()->get('Version'); ?>'
            ];

            cssFiles.forEach(function(href) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                link.media = 'print';
                link.onload = function() { this.media = 'all'; };
                document.head.appendChild(link);
            });
        })();
        </script>
        <?php
    }

    /**
     * Optimize CSS delivery
     */
    public function optimize_css_delivery($tag, $handle) {
        // Skip critical CSS files
        $critical_handles = array('inito-critical-css');
        
        if (in_array($handle, $critical_handles)) {
            return $tag;
        }

        // Load non-critical CSS asynchronously
        $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
        
        return $tag;
    }

    /**
     * Optimize JavaScript delivery
     */
    public function optimize_js_delivery($tag, $handle) {
        // Scripts that should be deferred
        $defer_scripts = array(
            'inito-lazy-loading',
            'inito-main',
            'wp-embed'
        );

        // Scripts that should be loaded async
        $async_scripts = array(
            'google-analytics',
            'gtag'
        );

        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script', '<script defer', $tag);
        }

        if (in_array($handle, $async_scripts)) {
            return str_replace('<script', '<script async', $tag);
        }

        return $tag;
    }

    /**
     * Add DNS prefetch and preconnect
     */
    public function add_dns_prefetch() {
        $dns_prefetch_domains = array(
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//www.google-analytics.com',
            '//stats.wp.com'
        );

        foreach ($dns_prefetch_domains as $domain) {
            echo '<link rel="dns-prefetch" href="' . esc_url($domain) . '">' . "\n";
        }
    }

    /**
     * Add preconnect for critical resources
     */
    public function add_preconnect() {
        $preconnect_domains = array(
            array('href' => '//fonts.googleapis.com', 'crossorigin' => true),
            array('href' => '//fonts.gstatic.com', 'crossorigin' => true)
        );

        foreach ($preconnect_domains as $domain) {
            $crossorigin = isset($domain['crossorigin']) && $domain['crossorigin'] ? ' crossorigin' : '';
            echo '<link rel="preconnect" href="' . esc_url($domain['href']) . '"' . $crossorigin . '>' . "\n";
        }
    }

    /**
     * Add resource hints
     */
    public function add_resource_hints() {
        // Prefetch next page if pagination exists
        if (is_home() || is_archive()) {
            $next_link = get_next_posts_link();
            if ($next_link) {
                preg_match('/href=[\'"]([^\'"]+)[\'"]/', $next_link, $matches);
                if (isset($matches[1])) {
                    echo '<link rel="prefetch" href="' . esc_url($matches[1]) . '">' . "\n";
                }
            }
        }
    }

    /**
     * Add resource hints filter
     */
    public function add_resource_hints_filter($urls, $relation_type) {
        switch ($relation_type) {
            case 'preconnect':
                $urls[] = 'https://fonts.googleapis.com';
                $urls[] = 'https://fonts.gstatic.com';
                break;
            case 'dns-prefetch':
                $urls[] = '//www.google-analytics.com';
                $urls[] = '//stats.wp.com';
                break;
        }

        return $urls;
    }

    /**
     * Optimize database queries
     */
    public function optimize_database_queries() {
        // Remove unnecessary queries
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');

        // Disable emoji scripts
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    /**
     * Optimize post queries
     */
    public function optimize_post_queries($clauses, $query) {
        if (!is_admin() && $query->is_main_query()) {
            // Add LIMIT to prevent runaway queries
            if (empty($clauses['limits']) && !$query->get('no_found_rows')) {
                $posts_per_page = $query->get('posts_per_page');
                if ($posts_per_page > 0) {
                    $clauses['limits'] = 'LIMIT 0, ' . absint($posts_per_page);
                }
            }
        }

        return $clauses;
    }

    /**
     * Optimize Core Web Vitals
     */
    public function optimize_web_vitals() {
        ?>
        <script>
        // Reduce CLS by reserving space for images
        (function() {
            var images = document.querySelectorAll('img[width][height]');
            images.forEach(function(img) {
                var ratio = (img.getAttribute('height') / img.getAttribute('width')) * 100;
                img.style.aspectRatio = img.getAttribute('width') + '/' + img.getAttribute('height');
            });
        })();

        // Optimize LCP by prioritizing hero elements
        (function() {
            var hero = document.querySelector('.hero-section, .wp-block-cover, .entry-header');
            if (hero) {
                hero.style.containIntrinsicSize = 'auto 400px';
            }
        })();
        </script>
        <?php
    }

    /**
     * Optimize block rendering
     */
    public function optimize_block_rendering($block_content, $block) {
        // Skip empty blocks
        if (empty(trim($block_content))) {
            return $block_content;
        }

        // Add performance hints to images in blocks
        if (isset($block['blockName']) && strpos($block['blockName'], 'image') !== false) {
            $block_content = str_replace('<img', '<img loading="lazy" decoding="async"', $block_content);
        }

        return $block_content;
    }

    /**
     * Setup object cache
     */
    public function setup_object_cache() {
        // Enable object caching for expensive operations
        if (!wp_using_ext_object_cache()) {
            wp_cache_add_global_groups(array('inito_performance'));
        }
    }

    /**
     * Optimize cache usage
     */
    public function optimize_cache_usage($value) {
        // Implement cache warming for critical data
        if (is_front_page() && !$value) {
            $this->warm_front_page_cache();
        }

        return $value;
    }

    /**
     * Get critical CSS
     */
    private function get_critical_css() {
        $critical_css = wp_cache_get('critical_css', 'inito_performance');
        
        if (false === $critical_css) {
            // Generate or load critical CSS
            $critical_css_file = get_template_directory() . '/assets/css/critical.css';
            
            if (file_exists($critical_css_file)) {
                $critical_css = file_get_contents($critical_css_file);
            } else {
                // Fallback critical CSS
                $critical_css = $this->generate_fallback_critical_css();
            }

            // Cache for 24 hours
            wp_cache_set('critical_css', $critical_css, 'inito_performance', DAY_IN_SECONDS);
        }

        return $critical_css;
    }

    /**
     * Generate fallback critical CSS
     */
    private function generate_fallback_critical_css() {
        return '
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
        .wp-block-group{max-width:1200px;margin:0 auto;}
        .wp-block-navigation{display:flex;gap:2rem;}
        .wp-site-blocks{min-height:100vh;}
        img{max-width:100%;height:auto;}
        .lazy-image{opacity:0;transition:opacity 0.3s;}
        .lazy-image.loaded{opacity:1;}
        ';
    }

    /**
     * Warm front page cache
     */
    private function warm_front_page_cache() {
        // Cache expensive front page queries
        $front_page_data = array(
            'recent_posts' => get_posts(array('numberposts' => 5)),
            'site_info' => array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
            )
        );

        wp_cache_set('front_page_data', $front_page_data, 'inito_performance', HOUR_IN_SECONDS);
    }

    /**
     * Check if WebP is supported
     */
    private function webp_supported() {
        return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }
}

// Initialize performance optimizations
new INITO_Performance();

/**
 * Performance utility functions
 */

/**
 * Get optimized image URL with WebP support
 */
function inito_get_optimized_image($attachment_id, $size = 'full') {
    $image = wp_get_attachment_image_src($attachment_id, $size);
    
    if (!$image) {
        return false;
    }

    // Check for WebP version
    $webp_url = str_replace(array('.jpg', '.jpeg', '.png'), '.webp', $image[0]);
    
    if (wp_http_validate_url($webp_url)) {
        $response = wp_remote_head($webp_url);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return $webp_url;
        }
    }

    return $image[0];
}

/**
 * Cache expensive theme operations
 */
function inito_cache_theme_content($content_key, $callback, $args = array()) {
    $cache_key = 'theme_content_' . md5($content_key . serialize($args));
    $cached_content = wp_cache_get($cache_key, 'inito_performance');

    if (false === $cached_content) {
        ob_start();
        call_user_func_array($callback, $args);
        $cached_content = ob_get_clean();
        
        wp_cache_set($cache_key, $cached_content, 'inito_performance', HOUR_IN_SECONDS);
    }

    echo $cached_content;
}

/**
 * Preload critical resources
 */
function inito_preload_resource($url, $type = 'script', $attributes = array()) {
    $preload_tag = '<link rel="preload" href="' . esc_url($url) . '" as="' . esc_attr($type) . '"';
    
    foreach ($attributes as $attr => $value) {
        $preload_tag .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
    }
    
    $preload_tag .= '>';
    
    echo $preload_tag . "\n";
}