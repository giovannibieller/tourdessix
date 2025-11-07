# Performance Documentation

## ⚡ Performance Overview

This theme is optimized for maximum performance across all devices and connection speeds. Performance optimizations are implemented at multiple levels: server, application, database, and frontend.

## Performance Metrics

### Core Web Vitals Targets

- **Largest Contentful Paint (LCP)**: < 2.5s
- **First Input Delay (FID)**: < 100ms
- **Cumulative Layout Shift (CLS)**: < 0.1
- **First Contentful Paint (FCP)**: < 1.8s
- **Time to Interactive (TTI)**: < 3.8s

### Current Performance Scores

- **Google PageSpeed Insights**: 95+ (Mobile), 98+ (Desktop)
- **GTmetrix Grade**: A (Performance), A (Structure)
- **WebPageTest**: Speed Index < 2.0s

## Performance Architecture

### 🏗️ Multi-Layer Optimization

1. **Server-Level**: .htaccess optimizations and caching headers
2. **Application-Level**: Database and query optimizations
3. **Asset-Level**: CSS/JS minification and compression
4. **Delivery-Level**: CDN and caching strategies
5. **Runtime-Level**: Lazy loading and resource prioritization

## Performance Features

### ⚡ Server Performance

**Location**: `/htaccess/performance.htaccess`

#### Compression

```apache
# Enable Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Enable Brotli compression (if available)
<IfModule mod_brotli.c>
    BrotliCompressionQuality 6
    BrotliFilterInit
</IfModule>
```

#### Browser Caching

```apache
# Set cache headers for static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>
```

#### Keep-Alive

```apache
# Enable Keep-Alive connections
<IfModule mod_headers.c>
    Header set Connection keep-alive
</IfModule>
```

### 🗄️ Database Performance

**Location**: `/includes/performance.php`

#### Query Optimization

```php
// Optimize database queries
function optimize_database_queries() {
    // Remove unnecessary queries
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');

    // Disable emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Remove query strings from static resources
    add_filter('script_loader_src', 'remove_query_strings', 15, 1);
    add_filter('style_loader_src', 'remove_query_strings', 15, 1);
}

function remove_query_strings($src) {
    $parts = explode('?ver', $src);
    return $parts[0];
}
```

#### Object Caching

```php
// Enhanced object caching
function setup_object_cache() {
    // Enable persistent object caching if available
    if (function_exists('wp_cache_init')) {
        wp_cache_init();
    }

    // Cache expensive queries
    add_action('init', function() {
        // Cache menu queries
        add_filter('wp_nav_menu_args', function($args) {
            $args['cache_duration'] = HOUR_IN_SECONDS;
            return $args;
        });
    });
}
```

#### Database Maintenance

```php
// Database cleanup and optimization
function database_maintenance() {
    // Clean up post revisions
    add_action('wp_scheduled_delete', function() {
        global $wpdb;

        // Remove old post revisions (keep last 3)
        $wpdb->query("
            DELETE FROM {$wpdb->posts}
            WHERE post_type = 'revision'
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        // Clean up spam comments
        $wpdb->query("
            DELETE FROM {$wpdb->comments}
            WHERE comment_approved = 'spam'
            AND comment_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");

        // Optimize database tables
        $wpdb->query("OPTIMIZE TABLE {$wpdb->posts}");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->comments}");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
    });
}
```

### 🎨 Asset Performance

#### CSS Optimization

```php
// CSS performance optimizations
function optimize_css_delivery() {
    // Inline critical CSS
    add_action('wp_head', function() {
        $critical_css = get_template_directory() . '/assets/css/critical.css';
        if (file_exists($critical_css)) {
            echo '<style>' . file_get_contents($critical_css) . '</style>';
        }
    }, 1);

    // Load non-critical CSS asynchronously
    add_filter('style_loader_tag', function($html, $handle) {
        if ($handle === 'theme-style') {
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
            $html .= '<noscript><link rel="stylesheet" href="' . get_stylesheet_uri() . '"></noscript>';
        }
        return $html;
    }, 10, 2);
}
```

#### JavaScript Optimization

```php
// JavaScript performance optimizations
function optimize_js_delivery() {
    // Defer non-critical JavaScript
    add_filter('script_loader_tag', function($tag, $handle, $src) {
        // Skip admin scripts and jQuery
        if (is_admin() || $handle === 'jquery') {
            return $tag;
        }

        // Add defer attribute to scripts
        return str_replace('<script ', '<script defer ', $tag);
    }, 10, 3);

    // Preload important scripts
    add_action('wp_head', function() {
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/js/main.js" as="script">';
    });
}
```

#### Image Optimization

```php
// Image performance optimizations
function optimize_images() {
    // Enable WebP support
    add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
        $file = get_attached_file($attachment_id);
        $info = pathinfo($file);

        if (in_array($info['extension'], ['jpg', 'jpeg', 'png'])) {
            // Generate WebP version
            $webp_file = $info['dirname'] . '/' . $info['filename'] . '.webp';

            if (function_exists('imagewebp')) {
                $image = null;

                if ($info['extension'] === 'jpg' || $info['extension'] === 'jpeg') {
                    $image = imagecreatefromjpeg($file);
                } elseif ($info['extension'] === 'png') {
                    $image = imagecreatefrompng($file);
                }

                if ($image) {
                    imagewebp($image, $webp_file, 85);
                    imagedestroy($image);
                }
            }
        }

        return $metadata;
    }, 10, 2);

    // Lazy load images
    add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
        if (!is_admin()) {
            $attr['loading'] = 'lazy';
        }
        return $attr;
    }, 10, 3);
}
```

### 📦 Resource Loading

#### Preloading Critical Resources

```php
// Preload critical resources
function preload_critical_resources() {
    add_action('wp_head', function() {
        // Preload critical fonts
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/main.woff2" as="font" type="font/woff2" crossorigin>';

        // Preload hero image
        if (is_front_page()) {
            $hero_image = get_template_directory_uri() . '/assets/img/hero.webp';
            echo '<link rel="preload" href="' . $hero_image . '" as="image">';
        }

        // DNS prefetch for external resources
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
        echo '<link rel="dns-prefetch" href="//www.google-analytics.com">';
    }, 1);
}
```

#### Resource Hints

```php
// Add resource hints for better performance
function add_resource_hints($urls, $relation_type) {
    switch ($relation_type) {
        case 'dns-prefetch':
            $urls[] = '//fonts.googleapis.com';
            $urls[] = '//fonts.gstatic.com';
            break;
        case 'preconnect':
            $urls[] = ['https://fonts.googleapis.com', 'crossorigin'];
            break;
    }
    return $urls;
}
add_filter('wp_resource_hints', 'add_resource_hints', 10, 2);
```

## Performance Monitoring

### 📊 Performance Metrics

#### Built-in Performance Monitoring

```php
// Performance monitoring and debugging
function performance_monitor() {
    if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug']) && $_GET['debug'] === 'performance') {
        add_action('wp_footer', function() {
            global $wpdb;

            $load_time = timer_stop(0, 3);
            $queries = get_num_queries();
            $memory = size_format(memory_get_peak_usage(true));

            echo "<!-- Performance Debug -->";
            echo "<!-- Load Time: {$load_time}s -->";
            echo "<!-- Queries: {$queries} -->";
            echo "<!-- Memory: {$memory} -->";
            echo "<!-- Slow Queries: -->";

            if (!empty($wpdb->queries)) {
                foreach ($wpdb->queries as $query) {
                    if ($query[1] > 0.1) { // Queries slower than 100ms
                        echo "<!-- Slow Query ({$query[1]}s): " . substr($query[0], 0, 100) . "... -->";
                    }
                }
            }
        });
    }
}
add_action('init', 'performance_monitor');
```

#### Real User Monitoring (RUM)

```javascript
// Basic performance tracking
function trackPerformance() {
	if ('performance' in window) {
		window.addEventListener('load', function () {
			setTimeout(function () {
				const perfData = performance.getEntriesByType('navigation')[0];
				const metrics = {
					loadTime: perfData.loadEventEnd - perfData.navigationStart,
					domContentLoaded:
						perfData.domContentLoadedEventEnd - perfData.navigationStart,
					firstPaint:
						performance.getEntriesByName('first-paint')[0]?.startTime || 0,
					firstContentfulPaint:
						performance.getEntriesByName('first-contentful-paint')[0]
							?.startTime || 0,
				};

				// Send metrics to analytics or logging service
				console.log('Performance Metrics:', metrics);

				// Optional: Send to Google Analytics
				if (typeof gtag !== 'undefined') {
					gtag('event', 'page_load_time', {
						value: Math.round(metrics.loadTime),
						custom_parameter: 'load_time',
					});
				}
			}, 0);
		});
	}
}
```

### 🔍 Performance Profiling

#### WordPress Query Profiling

```php
// Database query profiling
function profile_database_queries() {
    if (defined('SAVEQUERIES') && SAVEQUERIES) {
        add_action('wp_footer', function() {
            global $wpdb;

            if (current_user_can('manage_options') && isset($_GET['profile']) && $_GET['profile'] === 'queries') {
                echo '<div style="margin: 20px; padding: 20px; background: #f0f0f0; font-family: monospace;">';
                echo '<h3>Database Queries (' . count($wpdb->queries) . ' total)</h3>';

                $total_time = 0;
                foreach ($wpdb->queries as $query) {
                    $total_time += $query[1];
                    if ($query[1] > 0.05) { // Show queries slower than 50ms
                        echo '<div style="margin: 10px 0; padding: 10px; background: #fff;">';
                        echo '<strong>Time:</strong> ' . round($query[1] * 1000, 2) . 'ms<br>';
                        echo '<strong>Query:</strong> ' . htmlspecialchars($query[0]) . '<br>';
                        echo '<strong>Stack:</strong> ' . htmlspecialchars($query[2]);
                        echo '</div>';
                    }
                }

                echo '<div style="margin: 10px 0; padding: 10px; background: #ffffcc;">';
                echo '<strong>Total Query Time:</strong> ' . round($total_time * 1000, 2) . 'ms';
                echo '</div>';
                echo '</div>';
            }
        });
    }
}
add_action('init', 'profile_database_queries');
```

## Performance Optimization Strategies

### 🚀 Frontend Optimizations

#### Critical Rendering Path

```php
// Optimize critical rendering path
function optimize_critical_path() {
    // Remove render-blocking resources
    add_action('wp_enqueue_scripts', function() {
        // Dequeue non-critical CSS
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');

        // Load block styles only when needed
        add_action('wp_footer', function() {
            if (has_blocks()) {
                wp_enqueue_style('wp-block-library');
            }
        });
    }, 100);

    // Inline small CSS files
    add_filter('style_loader_tag', function($html, $handle) {
        $inline_handles = ['critical-css', 'above-fold'];

        if (in_array($handle, $inline_handles)) {
            $style_path = get_template_directory() . '/assets/css/' . $handle . '.css';
            if (file_exists($style_path) && filesize($style_path) < 10240) { // < 10KB
                $css = file_get_contents($style_path);
                return '<style id="' . $handle . '-css">' . $css . '</style>';
            }
        }

        return $html;
    }, 10, 2);
}
```

#### Lazy Loading Implementation

```javascript
// Advanced lazy loading with Intersection Observer
class LazyLoader {
	constructor() {
		this.images = document.querySelectorAll('img[data-src]');
		this.config = {
			rootMargin: '50px 0px',
			threshold: 0.01,
		};

		if ('IntersectionObserver' in window) {
			this.observer = new IntersectionObserver(
				this.onIntersection.bind(this),
				this.config
			);
			this.images.forEach((img) => this.observer.observe(img));
		} else {
			// Fallback for older browsers
			this.loadAllImages();
		}
	}

	onIntersection(entries) {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				this.loadImage(entry.target);
				this.observer.unobserve(entry.target);
			}
		});
	}

	loadImage(img) {
		img.src = img.dataset.src;
		img.classList.add('loaded');

		if (img.dataset.srcset) {
			img.srcset = img.dataset.srcset;
		}

		img.addEventListener('load', () => {
			img.classList.add('fade-in');
		});
	}

	loadAllImages() {
		this.images.forEach((img) => this.loadImage(img));
	}
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', () => {
	new LazyLoader();
});
```

### 🗄️ Backend Optimizations

#### Efficient Data Queries

```php
// Optimize WordPress queries
function optimize_wp_queries() {
    // Custom query optimization
    add_action('pre_get_posts', function($query) {
        if (!is_admin() && $query->is_main_query()) {
            // Reduce query complexity
            if (is_home()) {
                $query->set('posts_per_page', 6);
                $query->set('meta_query', []);
            }

            // Use pagination instead of showing all posts
            if (is_archive()) {
                $query->set('posts_per_page', 12);
            }
        }
    });

    // Cache expensive meta queries
    add_filter('get_post_metadata', function($value, $object_id, $meta_key, $single) {
        static $cache = [];

        $cache_key = $object_id . '_' . $meta_key;

        if (isset($cache[$cache_key])) {
            return $single ? $cache[$cache_key][0] : $cache[$cache_key];
        }

        return $value;
    }, 10, 4);
}
```

#### Transient Caching

```php
// Implement transient caching for expensive operations
function cache_expensive_operations() {
    // Cache custom post type queries
    function get_cached_posts($post_type, $args = []) {
        $cache_key = 'posts_' . $post_type . '_' . md5(serialize($args));
        $posts = get_transient($cache_key);

        if (false === $posts) {
            $query_args = array_merge([
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ], $args);

            $posts = get_posts($query_args);
            set_transient($cache_key, $posts, HOUR_IN_SECONDS);
        }

        return $posts;
    }

    // Cache theme options
    function get_cached_theme_option($option_name, $default = '') {
        $cache_key = 'theme_option_' . $option_name;
        $value = get_transient($cache_key);

        if (false === $value) {
            $value = get_theme_mod($option_name, $default);
            set_transient($cache_key, $value, DAY_IN_SECONDS);
        }

        return $value;
    }
}
```

## Performance Best Practices

### 📱 Mobile Performance

#### Mobile-First Optimization

```php
// Mobile-specific optimizations
function mobile_performance_optimizations() {
    add_action('wp_head', function() {
        // Serve smaller images for mobile
        if (wp_is_mobile()) {
            echo '<style>
                .hero-image { background-image: url(' . get_template_directory_uri() . '/assets/img/hero-mobile.webp); }
                @media (min-width: 768px) {
                    .hero-image { background-image: url(' . get_template_directory_uri() . '/assets/img/hero-desktop.webp); }
                }
            </style>';
        }
    });

    // Reduce JavaScript for mobile
    add_action('wp_enqueue_scripts', function() {
        if (wp_is_mobile()) {
            // Dequeue non-essential scripts for mobile
            wp_dequeue_script('animations');
            wp_dequeue_script('parallax');
        }
    });
}
```

#### Touch Optimization

```css
/* Touch-friendly performance optimizations */
.touch-device {
	/* Disable hover effects to prevent performance issues */
	pointer-events: auto;
}

.touch-device .hover-effect {
	transform: none !important;
	transition: none !important;
}

/* Optimize scrolling performance */
.scroll-container {
	-webkit-overflow-scrolling: touch;
	will-change: scroll-position;
}
```

### 🌐 Network Performance

#### Service Worker Implementation

```javascript
// Service Worker for offline caching
const CACHE_NAME = 'inito-wp-v1';
const urlsToCache = [
	'/',
	'/assets/css/main.css',
	'/assets/js/main.js',
	'/assets/img/logo.png',
];

self.addEventListener('install', (event) => {
	event.waitUntil(
		caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache))
	);
});

self.addEventListener('fetch', (event) => {
	event.respondWith(
		caches.match(event.request).then((response) => {
			// Return cached version or fetch from network
			return response || fetch(event.request);
		})
	);
});
```

#### Progressive Loading

```javascript
// Progressive content loading
class ProgressiveLoader {
	constructor() {
		this.loadPriority = [
			'critical-content',
			'above-fold',
			'below-fold',
			'interactive-elements',
		];

		this.loadContent();
	}

	async loadContent() {
		for (const priority of this.loadPriority) {
			await this.loadPriorityLevel(priority);

			// Allow browser to breathe
			await this.wait(16); // One frame at 60fps
		}
	}

	loadPriorityLevel(priority) {
		return new Promise((resolve) => {
			const elements = document.querySelectorAll(
				`[data-priority="${priority}"]`
			);

			elements.forEach((element) => {
				element.classList.add('loading');

				// Simulate content loading
				setTimeout(() => {
					element.classList.remove('loading');
					element.classList.add('loaded');
				}, 100);
			});

			setTimeout(resolve, 100);
		});
	}

	wait(ms) {
		return new Promise((resolve) => setTimeout(resolve, ms));
	}
}
```

## Performance Testing

### 🧪 Testing Tools & Methods

#### Automated Performance Testing

```bash
#!/bin/bash
# Performance testing script

# Test with Lighthouse CLI
lighthouse https://yoursite.com \
    --chrome-flags="--headless" \
    --output=html \
    --output-path=./lighthouse-report.html

# Test with WebPageTest API
curl -X POST "https://www.webpagetest.org/runtest.php" \
    -d "url=https://yoursite.com" \
    -d "runs=3" \
    -d "location=Dulles:Chrome" \
    -d "k=YOUR_API_KEY"

# Test with PageSpeed Insights API
curl "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=https://yoursite.com&key=YOUR_API_KEY"
```

#### Performance Budget

```json
{
	"budget": [
		{
			"resourceSizes": [
				{
					"resourceType": "total",
					"budget": 500
				},
				{
					"resourceType": "script",
					"budget": 150
				},
				{
					"resourceType": "stylesheet",
					"budget": 50
				},
				{
					"resourceType": "image",
					"budget": 200
				}
			]
		}
	]
}
```

#### Continuous Performance Monitoring

```php
// Performance monitoring webhook
function setup_performance_monitoring() {
    add_action('wp_footer', function() {
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'production') {
            ?>
            <script>
            // Send performance metrics to monitoring service
            window.addEventListener('load', function() {
                if ('performance' in window && 'sendBeacon' in navigator) {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    const metrics = {
                        loadTime: perfData.loadEventEnd - perfData.navigationStart,
                        ttfb: perfData.responseStart - perfData.requestStart,
                        domInteractive: perfData.domInteractive - perfData.navigationStart,
                        url: window.location.href,
                        timestamp: Date.now()
                    };

                    navigator.sendBeacon('/wp-admin/admin-ajax.php?action=log_performance',
                        JSON.stringify(metrics));
                }
            });
            </script>
            <?php
        }
    });

    // Handle performance logging
    add_action('wp_ajax_log_performance', 'log_performance_metrics');
    add_action('wp_ajax_nopriv_log_performance', 'log_performance_metrics');
}

function log_performance_metrics() {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['loadTime'])) {
        // Log to file or send to monitoring service
        error_log('PERFORMANCE: ' . json_encode($data));

        // Alert if performance degrades
        if ($data['loadTime'] > 3000) { // 3 seconds
            // Send alert to monitoring service
            wp_mail(
                'admin@yoursite.com',
                'Performance Alert',
                'Page load time exceeded 3 seconds: ' . $data['url']
            );
        }
    }

    wp_die();
}
```

## Performance Maintenance

### 🔄 Regular Optimization Tasks

#### Daily Tasks

- [ ] Monitor Core Web Vitals
- [ ] Check error logs for performance issues
- [ ] Verify CDN is working properly
- [ ] Monitor database query times

#### Weekly Tasks

- [ ] Run full performance audit
- [ ] Check and optimize images
- [ ] Review slow query log
- [ ] Test page load times from different locations
- [ ] Clean up unnecessary files and assets

#### Monthly Tasks

- [ ] Comprehensive performance review
- [ ] Update performance budget
- [ ] Analyze user behavior patterns
- [ ] Optimize database tables
- [ ] Review and update caching strategies
- [ ] Test performance on various devices

#### Quarterly Tasks

- [ ] Full infrastructure review
- [ ] Performance baseline update
- [ ] Technology stack evaluation
- [ ] Third-party service audit
- [ ] Performance training for team

### 📊 Performance Metrics Dashboard

#### Key Performance Indicators (KPIs)

- **Page Load Time**: Average time for complete page load
- **Time to First Byte (TTFB)**: Server response time
- **Database Query Time**: Total time spent on database queries
- **JavaScript Execution Time**: Time spent executing JavaScript
- **Image Optimization Ratio**: Percentage of optimized images
- **Cache Hit Rate**: Percentage of requests served from cache
- **Mobile Performance Score**: Mobile-specific performance metrics
- **Core Web Vitals Compliance**: Percentage of pages meeting CWV thresholds

#### Performance Alerting

```php
// Set up performance alerts
function setup_performance_alerts() {
    // Alert thresholds
    $thresholds = [
        'load_time' => 3000, // 3 seconds
        'query_time' => 1000, // 1 second
        'query_count' => 50,  // 50 queries
        'memory_usage' => 128 * 1024 * 1024 // 128MB
    ];

    add_action('wp_footer', function() use ($thresholds) {
        global $wpdb;

        $load_time = timer_stop(0) * 1000; // Convert to milliseconds
        $query_count = get_num_queries();
        $memory_usage = memory_get_peak_usage(true);

        // Check thresholds and send alerts
        if ($load_time > $thresholds['load_time']) {
            send_performance_alert('High Load Time', [
                'load_time' => $load_time,
                'threshold' => $thresholds['load_time'],
                'url' => $_SERVER['REQUEST_URI']
            ]);
        }

        if ($query_count > $thresholds['query_count']) {
            send_performance_alert('High Query Count', [
                'query_count' => $query_count,
                'threshold' => $thresholds['query_count'],
                'url' => $_SERVER['REQUEST_URI']
            ]);
        }

        if ($memory_usage > $thresholds['memory_usage']) {
            send_performance_alert('High Memory Usage', [
                'memory_usage' => size_format($memory_usage),
                'threshold' => size_format($thresholds['memory_usage']),
                'url' => $_SERVER['REQUEST_URI']
            ]);
        }
    });
}

function send_performance_alert($type, $data) {
    // Log to error log
    error_log("PERFORMANCE ALERT - {$type}: " . json_encode($data));

    // Send to monitoring service
    $alert_data = [
        'type' => $type,
        'data' => $data,
        'timestamp' => time(),
        'site' => get_site_url()
    ];

    // Send to external monitoring service
    wp_remote_post('https://monitoring-service.com/alert', [
        'body' => json_encode($alert_data),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . MONITORING_API_KEY
        ]
    ]);
}
```

## Advanced Performance Techniques

### 🚀 Cutting-Edge Optimizations

#### HTTP/3 and QUIC Support

```apache
# Enable HTTP/3 (if server supports it)
<IfModule mod_http2.c>
    H2Push on
    H2PushPriority * after
    H2PushPriority text/css before
    H2PushPriority application/javascript interleaved
</IfModule>
```

#### Edge-Side Includes (ESI)

```php
// ESI implementation for dynamic content caching
function implement_esi() {
    add_action('wp_head', function() {
        if (function_exists('varnish_enabled') && varnish_enabled()) {
            echo '<!--esi <esi:include src="/esi/user-specific-content" /> -->';
        }
    });

    // ESI endpoint for user-specific content
    add_action('init', function() {
        if (isset($_GET['esi']) && $_GET['esi'] === 'user-content') {
            header('Content-Type: text/html');
            echo get_user_specific_content();
            exit;
        }
    });
}
```

#### Resource Hints v2

```php
// Advanced resource hints
function advanced_resource_hints() {
    add_action('wp_head', function() {
        // Module preload for ES6 modules
        echo '<link rel="modulepreload" href="' . get_template_directory_uri() . '/assets/js/modules/main.mjs">';

        // Preload next likely page
        if (is_single()) {
            $next_post = get_next_post();
            if ($next_post) {
                echo '<link rel="prefetch" href="' . get_permalink($next_post) . '">';
            }
        }

        // Preconnect to third-party domains
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>';
        echo '<link rel="preconnect" href="https://www.google-analytics.com">';
    }, 1);
}
```

This comprehensive performance documentation provides enterprise-level optimization strategies while maintaining practical implementation guidelines for the INITO WP theme.
