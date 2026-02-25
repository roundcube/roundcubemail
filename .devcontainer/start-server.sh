#!/bin/bash
# Start Roundcube development server and open browser to appropriate page

PORT=${1:-8081}

# Determine the startup URL based on installer presence
if [ -f "installer/index.php" ] && [ -f "config/config.inc.php" ]; then
    # Check if enable_installer is set to true in config
    if grep -q "enable_installer.*=.*true" config/config.inc.php 2>/dev/null; then
        STARTUP_PATH="/installer/"
        echo "Installer is enabled - will open installer page"
    else
        STARTUP_PATH="/"
        echo "Installer is disabled - will open main page"
    fi
elif [ -f "installer/index.php" ] && [ ! -f "config/config.inc.php" ]; then
    STARTUP_PATH="/installer/"
    echo "No config found - will open installer page"
else
    STARTUP_PATH="/"
    echo "Will open main page"
fi

STARTUP_URL="http://localhost:${PORT}${STARTUP_PATH}"

echo ""
echo "Starting PHP development server on port ${PORT}..."
echo "URL: ${STARTUP_URL}"
echo ""

# Give the server a moment to start, then try to open the browser
(
    sleep 2
    # Try various methods to open the URL
    if command -v xdg-open &> /dev/null; then
        xdg-open "$STARTUP_URL" 2>/dev/null &
    elif command -v open &> /dev/null; then
        open "$STARTUP_URL" 2>/dev/null &
    fi
) &

# Start the PHP built-in server (this blocks)
exec php -S "0.0.0.0:${PORT}" -t public_html 2>&1
