#!/bin/bash

# Script to initialize "INITO WP | Starter Theme" for new projects
# - Reset git repository (remove connection to original repo)
# - Rename theme throughout the project
# Author: Giovanni Bieller

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to sanitize text for slug/identifier usage
sanitize_for_slug() {
    local text="$1"
    # Convert to lowercase, replace spaces and special chars with hyphens, remove multiple hyphens
    echo "$text" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g' | sed 's/-\+/-/g' | sed 's/^-\|-$//g'
}

# Welcome message
echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}    INITO WP Theme Initialization Script${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""
echo -e "${BLUE}This script will:${NC}"
echo "  1. Rename the theme throughout the project"
echo "  2. Reset the git repository (preserving commitlint configuration)"
echo "  3. Optionally set up your new git repository"
echo ""

# STEP 1: Get the new theme name and rename theme

# Function to reset git repository while preserving commitlint
reset_git_repository() {
    print_status "Resetting git repository..."
    
    if [ ! -d ".git" ]; then
        print_warning "No .git directory found. Initializing new git repository..."
    else
        # Get current remote URL to show user what will be removed
        CURRENT_REMOTE=$(git remote get-url origin 2>/dev/null || echo "No remote origin found")
        
        if [ "$CURRENT_REMOTE" != "No remote origin found" ]; then
            print_status "Removing connection to: ${YELLOW}${CURRENT_REMOTE}${NC}"
        fi
        
        print_status "Removing existing .git directory and commit history..."
        rm -rf .git
        
        if [ $? -eq 0 ]; then
            print_success "Git history removed successfully"
        else
            print_error "Failed to remove .git directory"
            return 1
        fi
    fi
    
    print_status "Initializing fresh git repository..."
    git init
    
    if [ $? -eq 0 ]; then
        print_success "New git repository initialized"
        
        # Ensure commitlint hooks are properly set up
        print_status "Setting up commitlint configuration..."
        if [ -f "package.json" ] && command -v npm >/dev/null 2>&1; then
            # Run husky to reinstall git hooks
            npm run prepare >/dev/null 2>&1
            if [ $? -eq 0 ]; then
                print_success "Commitlint hooks configured successfully"
            else
                print_warning "Could not automatically configure commitlint hooks. Run 'npm run prepare' manually."
            fi
        else
            print_warning "Could not set up commitlint hooks automatically. Ensure npm is available and run 'npm run prepare'."
        fi
        
        return 0
    else
        print_error "Failed to initialize new git repository"
        return 1
    fi
}

# Function to setup git remote and initial commit
setup_git_remote() {
    echo ""
    print_status "Git Repository Setup"
    echo ""
    read -p "Enter your new git repository URL (optional, press Enter to skip): " NEW_REPO_URL
    
    if [ ! -z "$NEW_REPO_URL" ]; then
        print_status "Adding remote origin: ${YELLOW}${NEW_REPO_URL}${NC}"
        git remote add origin "$NEW_REPO_URL"
        
        if [ $? -eq 0 ]; then
            print_success "Remote origin added successfully"
        else
            print_error "Failed to add remote origin"
            return 1
        fi
    else
        print_status "Skipping remote setup. You can add it later with:"
        echo -e "  ${BLUE}git remote add origin <your-repo-url>${NC}"
    fi
    
    # Create initial commit with the new theme name
    print_status "Creating initial commit..."
    git add .
    git commit -m "chore: initial commit: ${NEW_SLUG} theme setup"
    
    if [ $? -eq 0 ]; then
        print_success "Initial commit created"
        
        if [ ! -z "$NEW_REPO_URL" ]; then
            echo ""
            read -p "Do you want to push to the remote repository now? (y/N): " PUSH_NOW
            
            if [[ $PUSH_NOW =~ ^[Yy]$ ]]; then
                print_status "Pushing to remote repository..."
                git push -u origin main
                
                if [ $? -eq 0 ]; then
                    print_success "Successfully pushed to remote repository!"
                else
                    print_error "Failed to push to remote repository"
                    print_status "You can push later with: ${BLUE}git push -u origin main${NC}"
                fi
            else
                print_status "You can push later with: ${BLUE}git push -u origin main${NC}"
            fi
        fi
    else
        print_error "Failed to create initial commit"
        return 1
    fi
}

# STEP 1: Get the new theme name and rename theme
print_status "STEP 1: Theme Configuration"
echo ""

# Get the current theme name and slug
CURRENT_NAME="INITO WP | Starter Theme"
CURRENT_SLUG="inito-wp-theme"
print_status "Current theme name: ${YELLOW}${CURRENT_NAME}${NC}"
print_status "Current theme slug: ${YELLOW}${CURRENT_SLUG}${NC}"

# Prompt for new theme name
echo ""
read -p "Enter your new theme name: " NEW_NAME

# Validate input
if [ -z "$NEW_NAME" ]; then
    print_error "Theme name cannot be empty!"
    exit 1
fi

# Generate sanitized slug
NEW_SLUG=$(sanitize_for_slug "$NEW_NAME")

# Show generated slug and allow customization
echo ""
print_status "Generated theme slug: ${GREEN}${NEW_SLUG}${NC}"
read -p "Press Enter to use this slug, or type a custom slug: " CUSTOM_SLUG

if [ ! -z "$CUSTOM_SLUG" ]; then
    NEW_SLUG=$(sanitize_for_slug "$CUSTOM_SLUG")
    print_status "Using custom slug: ${GREEN}${NEW_SLUG}${NC}"
fi

# Confirm the change
echo ""
print_status "Theme will be renamed to:"
echo -e "  Name: ${GREEN}${NEW_NAME}${NC}"
echo -e "  Slug: ${GREEN}${NEW_SLUG}${NC}"
echo ""

read -p "Proceed with theme rename? (Y/n): " CONFIRM_RENAME
if [[ $CONFIRM_RENAME =~ ^[Nn]$ ]]; then
    print_status "Operation cancelled."
    exit 0
fi

print_status "Renaming theme files..."

# Function to replace text in file
replace_in_file() {
    local file="$1"
    local old_text="$2"
    local new_text="$3"
    local description="$4"
    
    if [ -f "$file" ]; then
        # Use sed to replace the text (macOS compatible) - using @ as delimiter to avoid conflicts with |
        if sed -i '' "s@${old_text}@${new_text}@g" "$file" 2>/dev/null; then
            print_success "Updated: $file ($description)"
        else
            print_error "Failed to update: $file ($description)"
        fi
    else
        print_warning "File not found: $file"
    fi
}

# Replace theme names
replace_in_file "package.json" "$CURRENT_NAME" "$NEW_NAME" "theme name in themeName field"
replace_in_file "style.css" "$CURRENT_NAME" "$NEW_NAME" "theme name in header"
replace_in_file "manifest.json" "$CURRENT_NAME" "$NEW_NAME" "app name"
replace_in_file "README.md" "$CURRENT_NAME" "$NEW_NAME" "title"

# Replace theme slugs/identifiers
replace_in_file "package.json" "$CURRENT_SLUG" "$NEW_SLUG" "package name"
replace_in_file "package-lock.json" "$CURRENT_SLUG" "$NEW_SLUG" "package name"
replace_in_file "style.css" "$CURRENT_SLUG" "$NEW_SLUG" "text domain"
replace_in_file "manifest.json" "$CURRENT_SLUG" "$NEW_SLUG" "short name"
replace_in_file "functions.php" "$CURRENT_SLUG" "$NEW_SLUG" "text domain"

print_success "Theme renamed successfully!"

# STEP 2: Reset Git Repository
echo ""
print_status "STEP 2: Git Repository Reset"
echo ""

if reset_git_repository; then
    print_success "Git repository reset completed!"
else
    print_error "Git repository reset failed!"
    exit 1
fi

# STEP 3: Setup new git repository
setup_git_remote

# Final Summary
echo ""
print_success "============================================"
print_success "    Initialization Completed Successfully!"
print_success "============================================"
echo ""
print_status "Summary:"
echo -e "  ✓ Theme renamed to: ${GREEN}${NEW_NAME}${NC}"
echo -e "  ✓ Theme slug: ${GREEN}${NEW_SLUG}${NC}"
echo -e "  ✓ Git repository reset with fresh history"
echo -e "  ✓ Commitlint configuration preserved and active"

# Show git status
if git remote get-url origin >/dev/null 2>&1; then
    REMOTE_URL=$(git remote get-url origin)
    echo -e "  ✓ Remote repository: ${GREEN}${REMOTE_URL}${NC}"
else
    echo -e "  • No remote repository configured"
fi

echo ""
print_status "Next steps:"
echo "  1. Test your WordPress theme"
echo "  2. Update any additional references in your code"
echo "  3. Clear any caches (npm, WordPress, etc.)"
echo "  4. Update translation files in /lang/ directory if needed"

if ! git remote get-url origin >/dev/null 2>&1; then
    echo "  5. Add your remote repository when ready:"
    echo -e "     ${BLUE}git remote add origin <your-repo-url>${NC}"
    echo -e "     ${BLUE}git push -u origin main${NC}"
fi

echo ""
print_success "Your theme is ready for development!"
