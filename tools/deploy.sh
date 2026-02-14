#!/bin/bash
#
# Deploy script for Lasater Salvage Inventory System
# Usage: ./tools/deploy.sh [user@host] [remote_path]
#
# Example: ./tools/deploy.sh lasater@server.dreamhost.com /home/lasater/inventory.lasater.com
#

set -e

REMOTE_HOST="${1:?Usage: deploy.sh user@host remote_path}"
REMOTE_PATH="${2:?Usage: deploy.sh user@host remote_path}"

echo "=== Lasater Inventory Deployment ==="
echo "Target: ${REMOTE_HOST}:${REMOTE_PATH}"
echo ""

# Step 1: Build production assets locally
echo ">> Building production assets..."
npm run build
echo "   Done."

# Step 2: Sync files to server (excluding dev-only files)
echo ">> Syncing files to server..."
rsync -avz --delete \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.claude' \
    --exclude='storage/app/backups' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='vendor' \
    ./ "${REMOTE_HOST}:${REMOTE_PATH}/"
echo "   Done."

# Step 3: Run remote commands
echo ">> Running remote setup commands..."
ssh "${REMOTE_HOST}" << REMOTE_COMMANDS
    cd ${REMOTE_PATH}

    # Install production dependencies
    composer install --no-dev --optimize-autoloader --no-interaction

    # Run migrations
    php artisan migrate --force

    # Clear and rebuild caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Create storage symlink if needed
    php artisan storage:link 2>/dev/null || true

    # Set permissions
    chmod -R 775 storage bootstrap/cache
REMOTE_COMMANDS

echo ""
echo "=== Deployment complete ==="
echo "Verify: https://$(echo ${REMOTE_PATH} | grep -oP '[^/]+$')"
