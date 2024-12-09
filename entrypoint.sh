#!/bin/bash
set -e

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install JavaScript dependencies
npm install --include=dev --omit=optional

# Execute the CMD from the Dockerfile
exec "$@"
