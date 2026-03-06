#!/bin/bash
set -e

# =============================================================================
# Roundcube Dev Container Setup Script
# =============================================================================
#
# This script handles database initialization and dependency installation
# for the Roundcube development container environment.
#
# FUTURE ENHANCEMENTS (not yet implemented):
# -----------------------------------------------------------------------------
# 1. LOGGING TO FILE
#    - Add --log-file option to capture all output for debugging failed setups
#    - Useful when postCreateCommand fails silently
#    - Implementation: tee output to /workspace/.devcontainer/setup.log
#
# 2. VERSION COMPATIBILITY CHECKING
#    - Compare schema version in database against expected version in codebase
#    - Detect when database migrations are needed after code updates
#    - Parse SQL/mysql.initial.sql for version and compare with DB system table
#    - Offer to run migration scripts if versions don't match
#
# 3. BACKUP BEFORE RESET
#    - Add --backup option to mysqldump before reset operations
#    - Store backups in .devcontainer/backups/ with timestamps
#    - Auto-cleanup old backups (keep last N)
#    - Useful for preserving test data during development
#
# 4. HEALTHCHECK VERIFICATION
#    - After initialization, verify core functionality works
#    - Test a simple query on each table to ensure schema is correct
#    - Report any permission or constraint issues
#
# 5. MULTI-DATABASE SUPPORT
#    - Support PostgreSQL and SQLite in addition to MySQL
#    - Auto-detect database type from environment or config
#    - Use appropriate SQL initialization file
#
# =============================================================================

# Usage: ./post-create.sh [OPTIONS]
#   (no args)    - Full setup: database + dependencies + config
#   --db-only    - Only run database setup
#   --db-reset   - Force reset and reinitialize database
#   --db-status  - Check and report database state only
#   --help       - Show this help message

show_help() {
    echo "Roundcube Dev Container Setup Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  (no args)    Full setup: database + dependencies + config"
    echo "  --db-only    Only run database setup (detect state and initialize if needed)"
    echo "  --db-reset   Force reset and reinitialize database (drops all tables)"
    echo "  --db-status  Check and report database state only (no changes)"
    echo "  --help       Show this help message"
    echo ""
}

# Function to check MariaDB readiness (not just connection, but ready for operations)
wait_for_mariadb() {
    echo "Waiting for MariaDB to be ready..."
    local max_tries=20
    local tries=0

    # Phase 1: Wait for basic connectivity
    until mariadb -h db -u roundcube -proundcube -e "SELECT 1" &> /dev/null; do
        tries=$((tries + 1))
        if [ $tries -ge $max_tries ]; then
            echo "ERROR: MariaDB did not become ready after $max_tries attempts"
            echo "Last connection attempt output:"
            mariadb -h db -u roundcube -proundcube -e "SELECT 1" 2>&1 || true
            nc -zv db 3306 2>&1 || echo "Cannot connect to db:3306"
            return 1
        fi
        echo "  Attempt $tries/$max_tries - waiting for MariaDB connection..."
        sleep 2
    done

    # Phase 2: Wait for MariaDB to be fully ready for DDL operations
    # Sometimes MariaDB accepts connections before it's ready for schema changes
    tries=0
    until mariadb -h db -u roundcube -proundcube roundcube -e "SELECT 1" &> /dev/null; do
        tries=$((tries + 1))
        if [ $tries -ge 10 ]; then
            echo "ERROR: Database 'roundcube' is not accessible"
            return 1
        fi
        echo "  Waiting for database 'roundcube' to be accessible..."
        sleep 2
    done

    echo "MariaDB is ready!"
    return 0
}

# Function to detect database state
# Returns: 0=uninitialized, 1=initialized, 2=partial/corrupt
detect_db_state() {
    # Check if system table exists (it's the last thing created and contains version)
    if mariadb -h db -u roundcube -proundcube roundcube -e "SELECT name FROM system WHERE name='roundcube-version'" &> /dev/null; then
        local version=$(mariadb -h db -u roundcube -proundcube roundcube -N -e "SELECT value FROM system WHERE name='roundcube-version'" 2>/dev/null)
        if [ -n "$version" ]; then
            echo "DB_STATE: Initialized (version: $version)"
            return 1
        fi
    fi

    # Check if any core tables exist (indicates partial initialization)
    local table_count=$(mariadb -h db -u roundcube -proundcube roundcube -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='roundcube'" 2>/dev/null)
    # Default to 0 if empty or non-numeric
    if [ -z "$table_count" ] || ! [[ "$table_count" =~ ^[0-9]+$ ]]; then
        table_count=0
    fi
    if [ "$table_count" -gt 0 ]; then
        echo "DB_STATE: Partial initialization detected ($table_count tables exist, but no version marker)"
        return 2
    fi

    echo "DB_STATE: Uninitialized (empty database)"
    return 0
}

# Function to initialize database
init_database() {
    echo "Initializing database schema..."

    # Run the initialization SQL
    if mariadb -h db -u roundcube -proundcube roundcube < SQL/mysql.initial.sql 2>&1; then
        echo "Database schema initialized successfully!"
        return 0
    else
        echo "ERROR: Database initialization failed"
        return 1
    fi
}

# Function to reset and reinitialize database
reset_database() {
    echo "Resetting database (dropping all tables)..."

    # Build a single SQL script to drop all tables (FOREIGN_KEY_CHECKS must be in same session)
    local tables=$(mariadb -h db -u roundcube -proundcube roundcube -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='roundcube'" 2>/dev/null)

    if [ -n "$tables" ]; then
        local drop_sql="SET FOREIGN_KEY_CHECKS=0;"
        for table in $tables; do
            echo "  Will drop table: $table"
            drop_sql="${drop_sql} DROP TABLE IF EXISTS \`$table\`;"
        done
        drop_sql="${drop_sql} SET FOREIGN_KEY_CHECKS=1;"

        # Execute all drops in a single session
        if ! mariadb -h db -u roundcube -proundcube roundcube -e "$drop_sql"; then
            echo "ERROR: Failed to execute DROP statements"
            echo "drop_sql was: $drop_sql"
            echo "Aborting before init_database to prevent inconsistent state"
            return 1
        fi
        echo "All tables dropped."
    else
        echo "No tables to drop."
    fi

    # Now initialize fresh
    init_database
}

# Main database setup logic
setup_database() {
    if ! wait_for_mariadb; then
        echo "WARNING: Skipping database setup due to MariaDB connection issues"
        echo "You can manually initialize later with: mariadb -h db -u roundcube -proundcube roundcube < SQL/mysql.initial.sql"
        return 1
    fi

    echo ""
    echo "Checking database state..."
    local db_state=0
    detect_db_state || db_state=$?

    case $db_state in
        0)  # Uninitialized
            init_database
            ;;
        1)  # Already initialized
            echo "Database is already initialized - no action needed"
            ;;
        2)  # Partial/corrupt
            echo ""
            echo "WARNING: Database is in a partial/inconsistent state."
            echo "This can happen if initialization was interrupted."
            echo ""
            echo "Options:"
            echo "  1. Reset and reinitialize (recommended for dev)"
            echo "  2. Skip (you can manually fix later)"
            echo ""
            # In non-interactive mode (like postCreateCommand), auto-reset for dev convenience
            if [ ! -t 0 ]; then
                echo "Non-interactive mode detected - auto-resetting database..."
                reset_database
            else
                read -p "Reset database? [Y/n] " -n 1 -r
                echo
                if [[ ! $REPLY =~ ^[Nn]$ ]]; then
                    reset_database
                else
                    echo "Skipping database reset. Manual intervention may be required."
                fi
            fi
            ;;
    esac
}

# Full setup: database + dependencies + config
full_setup() {
    echo "=== Roundcube Dev Container Setup ==="

    # Run database setup
    setup_database

    echo ""

    # Install PHP dependencies
    echo "Installing Composer dependencies..."
    composer install --prefer-dist --no-interaction

    # Download JavaScript libraries (jQuery, TinyMCE, CodeMirror)
    # Run this BEFORE npm install since it's independent and npm can fail on some systems
    echo "Installing JavaScript libraries..."
    ./bin/install-jsdeps.sh

    # Install JavaScript dependencies (for build tools like lessc)
    echo "Installing npm dependencies..."
    npm install --include=dev --omit=optional || {
        echo "WARNING: npm install failed. CSS compilation may not work."
        echo "You can retry manually with: npm install --include=dev --omit=optional"
    }

    # Compile LESS files to CSS for elastic skin
    echo "Compiling CSS from LESS sources..."
    npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/styles.less > skins/elastic/styles/styles.min.css
    npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/print.less > skins/elastic/styles/print.min.css
    npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/embed.less > skins/elastic/styles/embed.min.css

    # Create config file if it doesn't exist
    create_config
}

# Create config file if it doesn't exist
create_config() {
    if [ ! -f config/config.inc.php ]; then
        echo "Creating development configuration..."
        cat > config/config.inc.php << 'EOF'
<?php

// Local configuration for Roundcube Webmail

// Database connection string (DSN) for read+write operations
$config['db_dsnw'] = 'mysql://roundcube:roundcube@db/roundcube';

// IMAP server host (use starttls://host format for TLS)
$config['imap_host'] = 'mail:143';

// SMTP server host (for starttls://host format)
$config['smtp_host'] = 'mailhog:1025';

// SMTP username (if required)
$config['smtp_user'] = '';

// SMTP password (if required)
$config['smtp_pass'] = '';

// Provide an URL where a user can get support for this Roundcube installation
$config['support_url'] = '';

// This key is used for encrypting purposes (must be exactly 24 characters)
$config['des_key'] = 'rcmail-dev-container!24!';

// Name your service
$config['product_name'] = 'Roundcube Webmail (Dev)';

// List of active plugins
$config['plugins'] = [
    'archive',
    'zipdownload',
];

// Skin name
$config['skin'] = 'elastic';

// Development settings - enable debug logging
$config['sql_debug'] = true;
$config['imap_debug'] = true;
$config['smtp_debug'] = true;
$config['log_driver'] = 'stdout';

// Session lifetime in minutes
$config['session_lifetime'] = 60;

// Disable IP check for session (useful for development)
$config['ip_check'] = false;

// Default language
$config['language'] = 'en_US';

// Disable installer (database is auto-initialized by devcontainer)
$config['enable_installer'] = false;
EOF
        echo "Configuration created at config/config.inc.php"
    fi
}

# Show completion message
show_completion() {
    echo ""
    echo "=== Setup Complete ==="
    echo ""
    echo "Services available:"
    echo "  - Mailhog UI:     http://localhost:8025"
    echo "  - MariaDB:        localhost:3306 (user: roundcube, pass: roundcube)"
    echo "  - IMAP:           mail:143"
    echo ""
    echo "Test credentials:"
    echo "  - Username: testuser"
    echo "  - Password: testpass"
    echo ""
    echo "To start the development server:"
    echo "  - Press Ctrl+Shift+B (runs default build task)"
    echo "  - Or manually: php -S 0.0.0.0:<PORT> -t public_html"
    echo ""
    echo "VS Code will auto-forward the port and show a notification."
    echo ""
}

# MariaDB connection options
# No SSL required for local development container
MARIADB_OPTS=""

# Main entry point - parse arguments
case "${1:-}" in
    --help|-h)
        show_help
        exit 0
        ;;
    --db-only)
        echo "=== Database Setup Only ==="
        setup_database
        ;;
    --db-reset)
        echo "=== Force Database Reset ==="
        if ! wait_for_mariadb; then
            echo "ERROR: Cannot connect to MariaDB"
            exit 1
        fi
        reset_database
        ;;
    --db-status)
        echo "=== Database Status Check ==="
        if ! wait_for_mariadb; then
            echo "ERROR: Cannot connect to MariaDB"
            exit 1
        fi
        detect_db_state
        ;;
    "")
        # No arguments - full setup
        full_setup
        show_completion
        ;;
    *)
        echo "Unknown option: $1"
        echo "Use --help for usage information"
        exit 1
        ;;
esac
