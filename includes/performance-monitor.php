<?php
/**
 * Performance Monitoring and Reporting
 *
 * @package INITO_WP_Starter
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Monitor Class
 * Tracks and reports performance metrics
 */
class INITO_Performance_Monitor {

    private $metrics = array();
    private $start_time;

    public function __construct() {
        $this->start_time = microtime(true);
        $this->init_hooks();
    }

    /**
     * Initialize monitoring hooks
     */
    private function init_hooks() {
        // Track page load times
        add_action('wp_loaded', array($this, 'track_wp_loaded_time'));
        add_action('wp_footer', array($this, 'track_page_generation_time'));
        
        // Track database queries
        add_action('shutdown', array($this, 'track_database_metrics'));
        
        // Track memory usage
        add_action('wp_footer', array($this, 'track_memory_usage'));
        
        // Add performance admin bar
        if (current_user_can('manage_options')) {
            add_action('admin_bar_menu', array($this, 'add_performance_admin_bar'), 999);
        }
        
        // Performance debug mode
        if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug']) && $_GET['debug'] === 'performance') {
            add_action('wp_footer', array($this, 'output_performance_report'));
        }
    }

    /**
     * Track WordPress loaded time
     */
    public function track_wp_loaded_time() {
        $this->metrics['wp_loaded_time'] = microtime(true) - $this->start_time;
    }

    /**
     * Track page generation time
     */
    public function track_page_generation_time() {
        $this->metrics['page_generation_time'] = microtime(true) - $this->start_time;
    }

    /**
     * Track database metrics
     */
    public function track_database_metrics() {
        global $wpdb;
        
        // Safely get number of queries
        $this->metrics['database_queries'] = isset($wpdb->num_queries) ? $wpdb->num_queries : 0;
        
        // Calculate total query time from saved queries
        $total_query_time = 0;
        
        // Check if query logging is enabled and queries exist
        if (defined('SAVEQUERIES') && SAVEQUERIES && 
            isset($wpdb->queries) && is_array($wpdb->queries) && !empty($wpdb->queries)) {
            
            foreach ($wpdb->queries as $query) {
                // Ensure query is properly formatted array
                if (is_array($query) && isset($query[1]) && is_numeric($query[1])) {
                    $total_query_time += floatval($query[1]);
                }
            }
            
            // Store slow queries if any
            $slow_queries = array();
            foreach ($wpdb->queries as $query) {
                if (is_array($query) && isset($query[1]) && is_numeric($query[1]) && $query[1] > 0.05) {
                    $slow_queries[] = array(
                        'query' => isset($query[0]) ? $query[0] : 'Unknown query',
                        'time' => floatval($query[1]),
                        'caller' => isset($query[2]) ? $query[2] : 'Unknown caller'
                    );
                }
            }
            $this->metrics['slow_queries'] = $slow_queries;
        } else {
            // If SAVEQUERIES is not enabled, we can't track query time
            $this->metrics['slow_queries'] = array();
        }
        
        $this->metrics['database_time'] = $total_query_time;
    }

    /**
     * Track memory usage
     */
    public function track_memory_usage() {
        $this->metrics['memory_usage'] = array(
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->get_memory_limit()
        );
    }

    /**
     * Get memory limit in bytes
     */
    private function get_memory_limit() {
        $limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            if ($matches[2] == 'M') {
                $limit = $matches[1] * 1024 * 1024;
            } elseif ($matches[2] == 'K') {
                $limit = $matches[1] * 1024;
            } elseif ($matches[2] == 'G') {
                $limit = $matches[1] * 1024 * 1024 * 1024;
            }
        }
        return $limit;
    }

    /**
     * Add performance info to admin bar
     */
    public function add_performance_admin_bar($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $generation_time = round((microtime(true) - $this->start_time) * 1000, 2);
        $memory_usage = round(memory_get_usage() / 1024 / 1024, 2);
        
        global $wpdb;
        $queries = $wpdb->num_queries;

        $wp_admin_bar->add_menu(array(
            'id'    => 'performance-info',
            'title' => sprintf(
                'Performance: %sms | %sMB | %s queries',
                $generation_time,
                $memory_usage,
                $queries
            ),
            'href'  => add_query_arg('debug', 'performance'),
        ));
    }

    /**
     * Output detailed performance report
     */
    public function output_performance_report() {
        $metrics = $this->get_all_metrics();
        ?>
        <style>
        #inito-performance-report {
            position: fixed;
            top: 32px;
            left: 0;
            right: 0;
            background: #fff;
            border-bottom: 3px solid #0073aa;
            z-index: 99999;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-height: 400px;
            overflow-y: auto;
        }
        #inito-performance-report h3 {
            margin: 0 0 15px 0;
            color: #0073aa;
        }
        #inito-performance-report .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        #inito-performance-report .metric-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        #inito-performance-report .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        #inito-performance-report .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        #inito-performance-report .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }
        #inito-performance-report .slow-queries {
            background: #fff2cc;
            border: 1px solid #d4a017;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }
        #inito-performance-report .slow-query {
            margin-bottom: 10px;
            padding: 8px;
            background: #fff;
            border-radius: 2px;
            font-family: monospace;
            font-size: 11px;
        }
        </style>
        
        <div id="inito-performance-report">
            <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            <h3>🚀 Performance Report</h3>
            
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-value"><?php echo round($metrics['page_generation_time'] * 1000, 2); ?>ms</div>
                    <div class="metric-label">Page Generation Time</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value"><?php echo $metrics['database_queries']; ?></div>
                    <div class="metric-label">Database Queries</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value"><?php echo round($metrics['memory_usage']['current'] / 1024 / 1024, 2); ?>MB</div>
                    <div class="metric-label">Memory Usage</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value"><?php echo round($metrics['memory_usage']['peak'] / 1024 / 1024, 2); ?>MB</div>
                    <div class="metric-label">Peak Memory</div>
                </div>
            </div>

            <?php if (!empty($metrics['slow_queries'])): ?>
            <div class="slow-queries">
                <h4>⚠️ Slow Queries (>50ms)</h4>
                <?php foreach ($metrics['slow_queries'] as $query): ?>
                <div class="slow-query">
                    <strong><?php echo round($query['time'] * 1000, 2); ?>ms:</strong>
                    <div><?php echo esc_html($query['query']); ?></div>
                    <small>Called by: <?php echo esc_html($query['caller']); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div style="margin-top: 15px; font-size: 12px; color: #666;">
                Add <code>?debug=performance</code> to any URL to see this report.
            </div>
        </div>
        <?php
    }

    /**
     * Get all collected metrics
     */
    public function get_all_metrics() {
        // Ensure all metrics are collected
        $this->track_page_generation_time();
        $this->track_memory_usage();
        $this->track_database_metrics();
        
        return $this->metrics;
    }

    /**
     * Log performance data to file
     */
    public function log_performance_data() {
        if (!WP_DEBUG) {
            return;
        }

        $metrics = $this->get_all_metrics();
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'url' => $_SERVER['REQUEST_URI'],
            'metrics' => $metrics
        );

        $log_file = WP_CONTENT_DIR . '/inito-performance.log';
        error_log(json_encode($log_entry) . "\n", 3, $log_file);
    }
}

// Fix for wpdb::$query_time compatibility issue
add_action('init', function() {
    global $wpdb;
    
    // Add query_time property if it doesn't exist to prevent errors
    if (!property_exists($wpdb, 'query_time')) {
        $wpdb->query_time = 0;
    }
}, 1);

// Initialize performance monitoring
if (defined('WP_DEBUG') && WP_DEBUG) {
    new INITO_Performance_Monitor();
}

/**
 * Performance optimization utilities
 * Note: Image optimization functions are handled in performance.php
 */

/**
 * Database optimization functions
 */

/**
 * Clean up database on a schedule
 */
if (!function_exists('inito_database_cleanup')) {
function inito_database_cleanup() {
    global $wpdb;

    // Clean up revisions older than 30 days
    $wpdb->query("
        DELETE FROM {$wpdb->posts} 
        WHERE post_type = 'revision' 
        AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");

    // Clean up spam comments
    $wpdb->query("
        DELETE FROM {$wpdb->comments} 
        WHERE comment_approved = 'spam' 
        AND comment_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");

    // Clean up orphaned meta
    $wpdb->query("
        DELETE pm FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.ID IS NULL
    ");

    // Optimize database tables
    $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
    foreach ($tables as $table) {
        $wpdb->query("OPTIMIZE TABLE {$table[0]}");
    }
}
}

// Schedule weekly database cleanup
if (!wp_next_scheduled('inito_database_cleanup')) {
    wp_schedule_event(time(), 'weekly', 'inito_database_cleanup');
}
add_action('inito_database_cleanup', 'inito_database_cleanup');

/**
 * Cache optimization
 */
if (!function_exists('inito_setup_advanced_caching')) {
function inito_setup_advanced_caching() {
    // Object cache groups
    wp_cache_add_global_groups(array(
        'inito_performance',
        'inito_cache',
        'theme_options'
    ));

    // Cache frequently accessed data
    add_action('wp_loaded', function() {
        // Cache theme options
        $theme_options = wp_cache_get('theme_options', 'inito_cache');
        if (false === $theme_options) {
            $theme_options = array(
                'theme_mods' => get_theme_mods(),
                'site_options' => array(
                    'blogname' => get_bloginfo('name'),
                    'blogdescription' => get_bloginfo('description'),
                    'home_url' => home_url(),
                    'admin_email' => get_option('admin_email')
                )
            );
            wp_cache_set('theme_options', $theme_options, 'inito_cache', HOUR_IN_SECONDS);
        }
    });
}
}
add_action('init', 'inito_setup_advanced_caching');

/**
 * Asset optimization
 */
if (!function_exists('inito_optimize_assets')) {
function inito_optimize_assets() {
    // Preload critical fonts
    add_action('wp_head', function() {
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style">' . "\n";
    }, 1);

    // Add resource hints
    add_action('wp_head', function() {
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }, 1);
}
}
add_action('init', 'inito_optimize_assets');

/**
 * Performance testing functions
 */

/**
 * Run performance tests
 */
if (!function_exists('inito_run_performance_tests')) {
function inito_run_performance_tests() {
    $tests = array();

    // Test database performance
    $start = microtime(true);
    $posts = get_posts(array('numberposts' => 10));
    $tests['database_query_time'] = microtime(true) - $start;

    // Test file system performance
    $start = microtime(true);
    $theme_files = glob(get_template_directory() . '/*.php');
    $tests['filesystem_access_time'] = microtime(true) - $start;

    // Test cache performance
    $start = microtime(true);
    wp_cache_set('test_key', 'test_value', 'inito_performance');
    $cached_value = wp_cache_get('test_key', 'inito_performance');
    $tests['cache_performance'] = microtime(true) - $start;

    return $tests;
}
}

/**
 * Performance recommendations
 */
if (!function_exists('inito_get_performance_recommendations')) {
function inito_get_performance_recommendations() {
    $recommendations = array();

    // Check if object caching is enabled
    if (!wp_using_ext_object_cache()) {
        $recommendations[] = 'Consider enabling object caching (Redis/Memcached) for better performance.';
    }

    // Check if Gzip is enabled
    if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
        $recommendations[] = 'Enable Gzip compression on your server for faster page loads.';
    }

    // Check for too many plugins
    $active_plugins = get_option('active_plugins');
    if (count($active_plugins) > 20) {
        $recommendations[] = 'You have many active plugins. Consider deactivating unused ones.';
    }

    // Check database size
    global $wpdb;
    $db_size = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    
    if ($db_size > 100) {
        $recommendations[] = 'Your database is quite large. Consider cleaning up old revisions and spam comments.';
    }

    return $recommendations;
}
}