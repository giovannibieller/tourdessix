# HTACCESS Configuration Guide

## 📁 File Structure

The htaccess folder contains a modular configuration system for better maintainability:

```
htaccess/
├── .htaccess              # Main file that includes others + WordPress rules
├── security.htaccess      # Security-specific configurations
└── performance.htaccess   # Performance-specific configurations
```

## 🎯 Main Configuration (`.htaccess`)

The main `.htaccess` file:

- **Includes** both security and performance modules
- **Preserves** WordPress rewrite rules
- **Maintains** clean separation of concerns

```apache
Include security.htaccess
Include performance.htaccess

# WordPress core rules remain untouched
```

## 🔒 Security Configuration (`security.htaccess`)

### Protection Features:

- **File Protection**: wp-config.php, .htaccess, readme files
- **Directory Protection**: wp-includes, uploads folder
- **Security Headers**: XSS, clickjacking, content-type protection
- **Bot Blocking**: Suspicious user agents and request methods
- **Access Control**: Sensitive file extensions and backup files

### Key Security Rules:

- Blocks direct access to PHP files in uploads
- Prevents access to WordPress core files
- Implements comprehensive security headers
- Protects against common attack vectors

## ⚡ Performance Configuration (`performance.htaccess`)

### Optimization Features:

- **Compression**: Gzip/Deflate for all text-based content
- **Browser Caching**: Optimized cache headers for different file types
- **HTTP/2 Push**: Server push for critical resources
- **ETag Optimization**: Removed for better caching
- **MIME Types**: Proper content type declarations

### Cache Durations:

- **Images**: 1 year (immutable)
- **CSS/JS**: 1 month
- **Fonts**: 1 year
- **HTML**: 1 hour
- **Feeds**: 10 minutes

## 🚀 Deployment Instructions

### For Development:

1. **Keep files in htaccess/ folder** for version control
2. **Test configurations** locally first
3. **Use relative includes** for portability

### For Production:

1. **Copy htaccess/.htaccess** to WordPress root as `.htaccess`
2. **Copy security.htaccess** to WordPress root
3. **Copy performance.htaccess** to WordPress root
4. **Verify server modules** are available

### Server Requirements:

- `mod_rewrite` (for WordPress)
- `mod_headers` (for security/performance headers)
- `mod_expires` (for browser caching)
- `mod_deflate` (for compression)
- `mod_http2` (optional, for HTTP/2 push)

## 🔧 Customization Options

### Security Customization:

```apache
# Enable HSTS (uncomment when HTTPS is ready)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"

# Adjust CSP in includes/security.php
# Modify bot blocking rules as needed
```

### Performance Customization:

```apache
# Adjust cache durations
ExpiresByType text/css "access plus 6 months"  # Instead of 1 month

# Add custom preload headers
Header always set Link "</path/to/critical.js>; rel=preload; as=script"

# Enable/disable HTTP/2 push
H2Push off  # To disable
```

## ✅ Verification Checklist

### Security Verification:

- [ ] wp-config.php returns 403 when accessed directly
- [ ] .htaccess file is not accessible via browser
- [ ] Security headers are present in response
- [ ] Suspicious requests are blocked

### Performance Verification:

- [ ] Gzip compression is working (check response headers)
- [ ] Cache headers are properly set
- [ ] Static assets have long cache times
- [ ] Critical resources are preloaded

### Testing Commands:

```bash
# Test compression
curl -H "Accept-Encoding: gzip" -I https://yoursite.com/

# Test security headers
curl -I https://yoursite.com/

# Test file protection
curl -I https://yoursite.com/wp-config.php
```

## 🚨 Troubleshooting

### Common Issues:

1. **Internal Server Error (500)**

   - Check server error logs
   - Verify required modules are enabled
   - Test configurations one by one

2. **Headers Not Working**

   - Ensure `mod_headers` is enabled
   - Check for conflicting rules
   - Verify syntax is correct

3. **Caching Not Working**

   - Verify `mod_expires` is enabled
   - Check browser developer tools
   - Clear browser cache

4. **WordPress Breaks**
   - Ensure WordPress rules are last
   - Don't modify BEGIN/END WordPress section
   - Test with minimal configuration first

## 📈 Performance Impact

Expected improvements with current configuration:

- **File Size Reduction**: 60-80% via compression
- **Load Time Improvement**: 30-50% via caching
- **Server Load Reduction**: Fewer requests via caching
- **Core Web Vitals**: Better LCP, FID, and CLS scores

## 🔄 Maintenance

### Regular Tasks:

- **Monitor error logs** for security issues
- **Review cache hit rates** for performance
- **Update security rules** as needed
- **Test configurations** after server changes

### Backup Strategy:

- **Keep original files** in version control
- **Test changes** in staging environment
- **Have rollback plan** ready

This modular approach provides:
✅ **Better organization** of rules
✅ **Easier maintenance** and updates
✅ **Version control friendly** structure
✅ **Environment-specific** customization
✅ **Professional deployment** process
