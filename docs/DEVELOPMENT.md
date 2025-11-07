# Development Guide

## Development Workflow

### Getting Started

```bash
# Ensure correct Node.js version
nvm use

# Start development environment
npm start              # Full WP-ENV + Gulp watching
# OR
npm run startGulp     # Gulp watching only
```

### Available NPM Scripts

```bash
npm run init          # Initialize project with custom name
npm start             # Start WP-ENV + Gulp development server
npm run startGulp     # Start Gulp watching (no WP-ENV)
npm run build         # Production build
npm run stop          # Stop WP-ENV server
npm run commitlint    # Validate commit messages
npm test              # Run test suite
```

## Build System

### Gulp Configuration

The theme uses a modern ES module-based Gulp configuration (`gulpfile.mjs`):

**Available Gulp Tasks:**

```bash
gulp styles          # Compile Sass to CSS
gulp scripts         # Process JavaScript
gulp images          # Optimize images
gulp watch           # Watch for file changes
gulp build           # Production build
```

### Asset Pipeline

**SCSS Processing:**

- Source: `/assets/sass/`
- Output: `/assets/css/`
- Features: Autoprefixer, minification, source maps

**JavaScript Processing:**

- Source: `/assets/js/`
- Output: Processed in-place
- Features: ES6+ transpilation, minification

**Image Optimization:**

- Source: `/assets/img/`
- Features: Compression, WebP conversion

### File Structure

```
assets/
├── sass/
│   ├── main.scss              # Main stylesheet
│   ├── editor-styles.scss     # Block editor styles
│   └── components/
│       ├── _variables.scss
│       ├── _mixins.scss
│       ├── _reset.scss
│       ├── _typography.scss
│       ├── _colors.scss
│       ├── _common.scss
│       └── _helpers.scss
├── js/
│   ├── main.js               # Main theme JavaScript
│   ├── lazy-loading.js       # Image lazy loading
│   └── accessibility.js      # Accessibility enhancements
├── css/
│   └── critical.css          # Critical CSS
└── img/
    └── (optimized images)
```

## Git Workflow

### Conventional Commits

This project uses [Conventional Commits](https://www.conventionalcommits.org/) with automatic validation.

**Commit Format:**

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code formatting (not CSS)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `build`: Build system changes
- `ci`: CI configuration changes
- `chore`: Other changes

**Examples:**

```bash
feat: add new hero section layout
fix: resolve mobile navigation issue
docs: update installation guide
style: format PHP according to WordPress standards
refactor: modernize template structure
perf: optimize database queries
```

### Commit Validation

Commits are automatically validated using Husky and Commitlint:

```bash
# Check your last commit
npm run commitlint:check

# Test a commit message
echo "feat: add new feature" | npx commitlint
```

## Code Standards

### PHP Standards

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

```php
<?php
/**
 * Function description
 *
 * @param string $param Parameter description
 * @return string Return description
 */
function inito_function_name( $param ) {
    // Function body
    return $param;
}
```

### JavaScript Standards

Use modern ES6+ syntax:

```javascript
// Good
const initializeFeature = () => {
	const elements = document.querySelectorAll('.selector');
	elements.forEach((element) => {
		// Implementation
	});
};

// Use arrow functions and const/let
const config = {
	threshold: 0.1,
	rootMargin: '50px',
};
```

### CSS/SCSS Standards

Follow BEM methodology:

```scss
// Block
.site-header {
	// Block styles

	// Element
	&__navigation {
		// Element styles

		// Modifier
		&--mobile {
			// Modifier styles
		}
	}
}
```

## Template Development

### Template Hierarchy

The theme uses a modular includes-based system:

```php
<?php
// page.php example
include (TEMPLATEPATH . "/includes/utils.php");
include (TEMPLATEPATH . "/includes/head.php");
?>

<div class="main_container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <div class="main_container_int">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php
include (TEMPLATEPATH . "/includes/footer.php");
include (TEMPLATEPATH . "/includes/footer_scripts.php");
?>
```

### Include Files

**Key include files:**

- `includes/head.php` - HTML head section
- `includes/footer.php` - Footer section
- `includes/utils.php` - Utility functions
- `includes/seo.php` - SEO meta tags
- `includes/security.php` - Security features
- `includes/performance.php` - Performance optimizations

## Theme Development

### Template Customization

The theme uses a modular includes-based system. Create custom layouts by:

```php
// In your template file
get_template_part('includes/head');

// Your custom content
echo '<main class="custom-layout">';
// Add your content here
echo '</main>';

get_template_part('includes/footer');
```

### Adding New Features

Extend functionality through the includes system:

```php
// includes/custom-feature.php
function add_custom_feature() {
    // Your feature implementation
}
add_action('wp_head', 'add_custom_feature');
```

## Performance Development

### Critical CSS

Update critical CSS for above-the-fold content:

```scss
// assets/css/critical.css
/* Critical styles for immediate rendering */
.site-header,
.hero-section {
	/* Essential styles only */
}
```

### Lazy Loading Implementation

```javascript
// Image lazy loading
const lazyImages = document.querySelectorAll('img[data-src]');
const imageObserver = new IntersectionObserver((entries, observer) => {
	entries.forEach((entry) => {
		if (entry.isIntersecting) {
			const img = entry.target;
			img.src = img.dataset.src;
			img.classList.remove('lazy');
			observer.unobserve(img);
		}
	});
});

lazyImages.forEach((img) => imageObserver.observe(img));
```

## Testing

### Manual Testing Checklist

**Functionality:**

- [ ] All pages load without errors
- [ ] Navigation works correctly
- [ ] Forms submit properly
- [ ] Search functionality works

**Performance:**

- [ ] PageSpeed Insights scores > 90
- [ ] Images are lazy-loaded
- [ ] CSS and JS are minified
- [ ] Critical CSS is inlined

**Accessibility:**

- [ ] Tab navigation works
- [ ] Screen reader compatibility
- [ ] Color contrast meets WCAG AA
- [ ] Form labels are proper

**Browser Testing:**

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Automated Testing

```bash
# Run available tests
npm test

# Future: Add accessibility testing
npm run test:a11y

# Future: Add performance testing
npm run test:performance
```

## Debugging

### WordPress Debug Mode

Enable debug mode in `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

### Performance Debugging

```php
// Add to URL: ?debug=performance
if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'performance' ) {
    // Show performance metrics
}
```

### Browser Dev Tools

Use browser development tools to:

- Check console for JavaScript errors
- Analyze network performance
- Test responsive design
- Verify accessibility

## Deployment Preparation

### Pre-deployment Checklist

1. **Build Assets:**

   ```bash
   npm run build
   ```

2. **Code Review:**

   - Remove debug statements
   - Check for console.log statements
   - Verify error handling

3. **Performance Check:**

   - Test with production data
   - Verify caching works
   - Check Core Web Vitals

4. **Security Review:**
   - Verify security headers
   - Test access restrictions
   - Check file permissions

### Files to Exclude from Production

```gitignore
node_modules/
.git/
.gitignore
gulpfile.mjs
package.json
package-lock.json
README.md
docs/
```

## Contribution Guidelines

### Pull Request Process

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Make your changes
4. Follow commit message format
5. Test your changes
6. Submit pull request

### Code Review Checklist

- [ ] Follows WordPress coding standards
- [ ] Includes proper documentation
- [ ] No console.log statements
- [ ] Performance impact considered
- [ ] Accessibility tested
- [ ] Browser compatibility verified

## Getting Help

**Development Issues:**

- Check [Troubleshooting](TROUBLESHOOTING.md)
- Review WordPress error logs
- Use browser developer tools

**Community:**

- GitHub Issues for bug reports
- GitHub Discussions for questions
- WordPress.org forums for general WP help
