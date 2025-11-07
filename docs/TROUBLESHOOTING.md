# Troubleshooting Guide

## Common Issues & Solutions

### 🔧 Installation Issues

#### Node.js Version Problems

**Issue**: Build fails with Node.js version errors

```bash
Error: The engine "node" is incompatible with this module
```

**Solution**:

```bash
# Install correct Node.js version
nvm install 20
nvm use 20

# Verify version
node --version  # Should show v20.x.x

# Clear npm cache and reinstall
npm cache clean --force
rm -rf node_modules
npm install
```

#### Permission Issues

**Issue**: npm install fails with permission errors

**Solution**:

```bash
# Fix npm permissions (macOS/Linux)
sudo chown -R $(whoami) ~/.npm
sudo chown -R $(whoami) node_modules

# Or use npm's recommended approach
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.profile
source ~/.profile
```

#### Gulp Not Found

**Issue**: `gulp: command not found`

**Solution**:

```bash
# Install Gulp CLI globally
npm install -g gulp-cli

# Or use npx
npx gulp watch

# Verify Gulp is installed locally
./node_modules/.bin/gulp --version
```

### 🎨 Build System Issues

#### Sass Compilation Errors

**Issue**: SCSS files not compiling or errors in compilation

**Solution**:

1. **Check Sass syntax**:

   ```bash
   # Test specific file
   npx sass assets/sass/main.scss assets/css/test.css
   ```

2. **Common SCSS errors**:

   - Missing semicolons
   - Incorrect nesting
   - Invalid CSS properties
   - Missing imports

3. **Reset build system**:
   ```bash
   rm -rf node_modules
   npm install
   npm run build
   ```

#### JavaScript Build Errors

**Issue**: JS files not processing or syntax errors

**Solution**:

1. **Check JavaScript syntax**:

   ```bash
   # Lint JavaScript files
   npx eslint assets/js/
   ```

2. **Common JS errors**:

   - ES6+ syntax in older browsers
   - Missing semicolons
   - Undefined variables
   - Import/export issues

3. **Debug build process**:
   ```bash
   # Run Gulp with verbose output
   npx gulp scripts --verbose
   ```

#### Assets Not Loading

**Issue**: CSS/JS files not loading on frontend

**Solution**:

1. **Check file paths**:

   ```php
   // Verify in functions.php
   wp_enqueue_style('theme-style', get_template_directory_uri() . '/assets/css/main.css');
   ```

2. **Check file permissions**:

   ```bash
   # Make files readable
   chmod 644 assets/css/*
   chmod 644 assets/js/*
   ```

3. **Clear caches**:
   - Browser cache
   - WordPress cache plugins
   - Server-side caching

### 🐛 WordPress Issues

#### Theme Not Appearing

**Issue**: Theme doesn't appear in WordPress admin

**Solution**:

1. **Check style.css header**:

   ```css
   /*
   Theme Name: INITO WP Starter
   Description: Theme description
   Version: 1.0.0
   */
   ```

2. **Check file structure**:

   ```
   inito-wp/
   ├── style.css     # Required
   ├── index.php     # Required
   ├── functions.php
   └── ...
   ```

3. **Check PHP syntax**:
   ```bash
   # Test PHP syntax
   php -l functions.php
   php -l index.php
   ```

#### PHP Errors

**Issue**: WordPress shows white screen or PHP errors

**Solution**:

1. **Enable WordPress debugging**:

   ```php
   // wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **Check error logs**:

   - `/wp-content/debug.log`
   - Server error logs
   - Browser console

3. **Common PHP issues**:
   - Missing closing PHP tags
   - Syntax errors in functions.php
   - Undefined functions or variables

#### Template Issues

**Issue**: Pages using wrong templates or showing errors

**Solution**:

1. **Check template hierarchy**:

   ```php
   // Debug which template is being used
   add_action('wp_head', function() {
       if (current_user_can('administrator')) {
           global $template;
           echo '<!-- Template: ' . basename($template) . ' -->';
       }
   });
   ```

2. **Verify include files exist**:

   ```bash
   ls -la includes/
   # Should show: head.php, footer.php, utils.php, etc.
   ```

3. **Check include paths**:
   ```php
   // Use WordPress functions instead of TEMPLATEPATH
   get_template_directory() . '/includes/head.php'
   ```

### 🛡️ Security Issues

#### Security Headers Not Working

**Issue**: Security headers not appearing in browser

**Solution**:

1. **Check if headers are being set**:

   ```bash
   # Test with curl
   curl -I https://yoursite.com

   # Or use online tools
   # securityheaders.com
   ```

2. **Verify include is loaded**:

   ```php
   // Add to functions.php for debugging
   if (file_exists(get_template_directory() . '/includes/security.php')) {
       echo '<!-- Security include loaded -->';
   }
   ```

3. **Check server conflicts**:
   - Apache modules
   - Nginx configuration
   - Cloudflare settings
   - Other plugins

#### CSP Violations

**Issue**: Content Security Policy blocking resources

**Solution**:

1. **Check browser console** for CSP violations

2. **Update CSP in `/includes/security.php`**:

   ```php
   // Add your domains to CSP
   $csp .= "script-src 'self' yourdomain.com; ";
   ```

3. **Test CSP gradually**:
   ```php
   // Start with report-only mode
   header('Content-Security-Policy-Report-Only: ' . $csp);
   ```

#### .htaccess Issues

**Issue**: .htaccess rules not working

**Solution**:

1. **Check if mod_rewrite is enabled**:

   ```php
   // Test in WordPress admin
   if (function_exists('apache_get_modules')) {
       $modules = apache_get_modules();
       var_dump(in_array('mod_rewrite', $modules));
   }
   ```

2. **Test .htaccess syntax**:

   ```bash
   # Check syntax
   apache2ctl configtest
   ```

3. **Check file permissions**:
   ```bash
   chmod 644 .htaccess
   ```

### ⚡ Performance Issues

#### Slow Page Loading

**Issue**: Pages loading slowly

**Solution**:

1. **Enable performance debugging**:

   ```
   Add ?debug=performance to any URL
   ```

2. **Check database queries**:

   ```php
   // Install Query Monitor plugin
   // Check for slow queries and N+1 problems
   ```

3. **Profile with tools**:
   - Google PageSpeed Insights
   - GTmetrix
   - WebPageTest

#### High Memory Usage

**Issue**: WordPress running out of memory

**Solution**:

1. **Increase memory limit**:

   ```php
   // wp-config.php
   ini_set('memory_limit', '256M');
   ```

2. **Check for memory leaks**:

   ```php
   // Add to functions.php for debugging
   add_action('wp_footer', function() {
       echo '<!-- Memory: ' . memory_get_peak_usage(true) / 1024 / 1024 . 'MB -->';
   });
   ```

3. **Review plugin conflicts**:
   - Deactivate plugins one by one
   - Check for resource-heavy plugins

#### Caching Issues

**Issue**: Changes not appearing or caching problems

**Solution**:

1. **Clear all caches**:

   ```bash
   # Browser cache (Ctrl+F5)
   # WordPress cache plugins
   # Server-side cache (Varnish, etc.)
   # CDN cache (Cloudflare, etc.)
   ```

2. **Check object cache**:
   ```php
   // Test object cache
   wp_cache_set('test', 'value');
   $result = wp_cache_get('test');
   var_dump($result); // Should show 'value'
   ```

### ♿ Accessibility Issues

#### Keyboard Navigation Problems

**Issue**: Site not accessible via keyboard

**Solution**:

1. **Test tab navigation**:

   - All interactive elements should be reachable
   - Focus indicators should be visible
   - Tab order should be logical

2. **Check ARIA attributes**:

   ```html
   <!-- Ensure proper ARIA labels -->
   <button aria-label="Close menu">×</button>
   <nav aria-label="Primary navigation"></nav>
   ```

3. **Verify skip links**:
   ```html
   <!-- Should be first element in body -->
   <a class="skip-link" href="#main">Skip to main content</a>
   ```

#### Screen Reader Issues

**Issue**: Content not accessible to screen readers

**Solution**:

1. **Test with screen readers**:

   - NVDA (Windows)
   - JAWS (Windows)
   - VoiceOver (macOS)

2. **Check semantic HTML**:

   ```html
   <!-- Use proper heading hierarchy -->
   <h1>Page Title</h1>
   <h2>Section Title</h2>
   <h3>Subsection Title</h3>
   ```

3. **Verify form labels**:
   ```html
   <!-- Every input needs a label -->
   <label for="email">Email Address</label>
   <input type="email" id="email" name="email" />
   ```

### 🔍 Debugging Tools

#### WordPress Debugging

```php
// wp-config.php - Enable all debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
```

#### Browser Developer Tools

**Console Tab**:

- JavaScript errors
- Network requests
- Performance metrics

**Network Tab**:

- Failed resource loads
- Slow loading assets
- HTTP status codes

**Lighthouse Tab**:

- Performance audit
- Accessibility audit
- SEO recommendations

#### WordPress Plugins for Debugging

- **Query Monitor**: Database and performance debugging
- **Debug Bar**: WordPress debugging information
- **Health Check**: Site health and troubleshooting

### 📞 Getting Additional Help

#### Before Seeking Help

1. **Check error logs** (WordPress and server)
2. **Test with default theme** to isolate issues
3. **Disable plugins** to check for conflicts
4. **Clear all caches**
5. **Try in incognito/private browsing mode**

#### Where to Get Help

**Theme-Specific Issues**:

- GitHub Issues: [Repository Issues](repository-issues-url)
- GitHub Discussions: [Repository Discussions](repository-discussions-url)

**WordPress General Issues**:

- [WordPress Support Forums](https://wordpress.org/support/)
- [WordPress Developer Documentation](https://developer.wordpress.org/)
- [WordPress Stack Exchange](https://wordpress.stackexchange.com/)

**Development Issues**:

- [Stack Overflow](https://stackoverflow.com/) (tag: wordpress)
- [WordPress Slack](https://make.wordpress.org/chat/)
- Local WordPress meetups and communities

#### Information to Provide When Seeking Help

1. **Error messages** (exact text)
2. **WordPress version**
3. **PHP version**
4. **Theme version**
5. **Active plugins list**
6. **Steps to reproduce the issue**
7. **Browser and operating system**
8. **Server environment** (shared hosting, VPS, etc.)

### 🛠️ Emergency Recovery

#### Site Completely Broken

1. **Switch to default theme**:

   ```bash
   # Via FTP/cPanel, rename theme folder
   mv inito-wp inito-wp-backup
   ```

2. **Restore from backup**:

   - Use hosting provider's backup
   - Restore from staging site
   - Use WordPress backup plugin

3. **Check recent changes**:

   ```bash
   # Git history
   git log --oneline -10

   # Revert recent commit if needed
   git revert HEAD
   ```

#### Database Issues

1. **Check database connectivity**:

   ```php
   // Test database connection
   $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
   if (!$connection) {
       die('Connection failed: ' . mysqli_connect_error());
   }
   ```

2. **Repair database**:

   ```php
   // wp-config.php
   define('WP_ALLOW_REPAIR', true);
   // Visit: yoursite.com/wp-admin/maint/repair.php
   ```

3. **Contact hosting provider** if database issues persist
