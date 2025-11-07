# Installation & Setup Guide

## Prerequisites

### System Requirements

- **Node.js**: 20 LTS or higher
- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3 or higher

### Recommended Tools

- **NVM**: For Node.js version management
- **Git**: For version control
- **Local Development Environment**:
  - Local by Flywheel
  - XAMPP/MAMP
  - Docker with WP-ENV

## Installation Steps

### 1. Clone the Repository

```bash
# Clone to your WordPress themes directory
git clone <repository-url> wp-content/themes/inito-wp
cd wp-content/themes/inito-wp
```

### 2. Node.js Setup

```bash
# Use the correct Node.js version (if using NVM)
nvm use

# Install dependencies
npm install
```

### 3. Theme Initialization

```bash
# Run the initialization script to set up your custom theme
npm run init
```

The initialization script will guide you through a 3-step process:

**Step 1: Theme Configuration**

- Enter your new theme name (e.g., "My Awesome Theme")
- Automatically generates a theme slug (or customize it)
- Updates all theme files with your branding:
  - `package.json` (name and themeName)
  - `style.css` (theme header and text domain)
  - `manifest.json` (app name and short name)
  - `README.md` (title)
  - `functions.php` (text domain)

**Step 2: Git Repository Reset**

- Automatically removes the connection to the original INITO WP repository
- Deletes existing `.git` directory and commit history
- Initializes a fresh git repository for your project

**Step 3: New Git Repository Setup (Optional)**

- Enter your new git repository URL (GitHub, GitLab, etc.)
- Automatically adds it as remote origin
- Creates an initial commit with your theme name
- Optionally pushes to your new repository immediately

After initialization, your theme will be completely rebranded and ready for development with no connection to the original repository.

### 4. Build Assets

```bash
# Development build
npm run startGulp

# Production build
npm run build
```

### 5. WordPress Setup

1. **Activate the theme** in WordPress admin
2. **Configure menus**: Go to Appearance > Menus and set up Primary and Footer menus
3. **Import ACF fields** (if using): Import JSON files from `/imports/` folder
4. **Set up widgets**: Configure footer widgets if needed

## First-Time Configuration

### Theme Customizer

Navigate to **Appearance > Customize** to configure:

1. **Site Identity**

   - Upload logo
   - Set site title and tagline
   - Configure site icon

2. **Menus**

   - Create and assign Primary menu
   - Create and assign Footer menu

3. **Widgets**
   - Set up Footer Widget Area
   - Configure sidebar widgets (if enabled)

### Security Setup

1. **Review security settings** in `/includes/security.php`
2. **Customize Content Security Policy** for your specific needs
3. **Verify .htaccess files** are properly configured

### Performance Configuration

1. **Enable object caching** (Redis/Memcached recommended)
2. **Configure CDN** if using one
3. **Set up performance monitoring** tools

## Development Environment Setup

### Using WP-ENV (Recommended)

```bash
# Start local WordPress environment
npm start

# Stop environment
npm run stop
```

### Manual Setup

If not using WP-ENV:

1. Set up your local WordPress installation
2. Clone theme to `wp-content/themes/`
3. Run `npm run startGulp` for asset watching

## Verification

After installation, verify everything is working:

1. **Frontend**: Visit your site and check for any console errors
2. **Backend**: Ensure no PHP errors in WordPress admin
3. **Build System**: Run `npm run build` and verify assets are generated
4. **Security Headers**: Use online tools to verify security headers are applied

## Troubleshooting

### Common Installation Issues

**Node.js Version Mismatch:**

```bash
nvm install 20
nvm use 20
```

**Permission Issues:**

```bash
sudo chown -R $(whoami) node_modules
npm install
```

**Build Errors:**

```bash
npm cache clean --force
rm -rf node_modules
npm install
```

### WordPress Issues

**Theme Not Appearing:**

- Check file permissions
- Verify PHP syntax in theme files
- Check WordPress error logs

**Missing Styles:**

- Run `npm run build`
- Check if CSS files are generated
- Verify asset enqueuing in functions.php

## Next Steps

Once installed:

1. Read the [Development Guide](DEVELOPMENT.md)
2. Review [Security Configuration](SECURITY.md)
3. Check [Performance Optimization](PERFORMANCE.md)
4. Explore [Customization Options](CUSTOMIZATION.md)
