# Tour des Six | WP Theme

A modern, performance-optimized Tour des Six | WP Theme with comprehensive security features, accessibility compliance, and enterprise-grade development practices.

## 🚀 Key Features

- **🎨 Modern WordPress 6.x**: Full block editor support with modern theme architecture
- **🛡️ Security Hardened**: Comprehensive headers, CSP implementation, and vulnerability protection
- **⚡ Performance Optimized**: Core Web Vitals optimized with lazy loading and caching
- **♿ Accessibility Ready**: WCAG AA compliance with keyboard navigation and screen reader support
- **🏗️ Modern Build System**: ES modules with Gulp and Node.js 20 LTS
- **📝 Conventional Commits**: Commitlint configuration with Husky hooks

## 🎯 Quick Start

### Prerequisites

- Node.js 20 LTS
- WordPress 6.0+
- PHP 8.0+

## 📚 Documentation

Our comprehensive documentation is organized into focused guides:

- **[📦 Installation Guide](docs/INSTALLATION.md)** - Setup, prerequisites, and configuration
- **[🛠️ Development Guide](docs/DEVELOPMENT.md)** - Workflow, scripts, and coding standards
- **[✨ Features Overview](docs/FEATURES.md)** - Complete feature documentation
- **[🛡️ Security Guide](docs/SECURITY.md)** - Security features and best practices
- **[⚡ Performance Guide](docs/PERFORMANCE.md)** - Optimization strategies and monitoring
- **[🔧 Troubleshooting](docs/TROUBLESHOOTING.md)** - Common issues and solutions

## Additional needed Actions

- Install `Advanced Custom Fields PRO`
- Import `imports/acf_seo.json` to enable ACF SEO FIELDS
- Create `Utils` ACF Group related to `Utils Option page`

## 🏗️ Architecture

### Template System

Uses modular includes-based architecture:

- `includes/head.php` - HTML head with SEO and meta tags
- `includes/footer.php` - Footer section with scripts
- `includes/security.php` - Security features and headers
- `includes/performance.php` - Performance optimizations
- `includes/accessibility.php` - WCAG compliance features

### Tech Stack

- **Build**: Gulp with ES modules (`gulpfile.mjs`)
- **CSS**: Sass with modular components
- **JS**: Modern ES6+ with performance optimizations
- **PHP**: 8.0+ compatible with WordPress best practices

## 🤝 Contributing

This project uses [Conventional Commits](https://www.conventionalcommits.org/):

```bash
feat: add new hero section layout
fix: resolve mobile navigation accessibility issue
docs: update installation instructions
perf: optimize image loading performance
```

See [DEVELOPMENT.md](docs/DEVELOPMENT.md) for detailed contribution guidelines.

## 📄 License

GPL v2 or later

---

Made with ❤️ by Giovanni Bieller
