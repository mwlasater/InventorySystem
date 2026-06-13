#!/bin/bash
#
# Deploy script for Lasater Salvage Inventory System
# Usage: ./tools/deploy.sh [user@host] [remote_path]
#
# Example: ./tools/deploy.sh lasater@server.dreamhost.com /home/lasater/inventory.lasater.com
#
# Before running:
#   1. Check out the release commit locally — this script ships your current
#      working tree, not a branch: `git checkout main && git pull`.
#   2. Make sure the *production* .env (which is NOT synced) is up to date.
#      `php artisan config:cache` below bakes .env in at deploy time, so any new
#      settings (e.g. MAIL_PASSWORD, ENFORCE_2FA_FOR_ADMINS) must already be set
#      on the server or they won't take effect.
#   Note: do not rotate APP_KEY once users have enrolled in 2FA — their encrypted
#   secrets/recovery codes would become undecryptable.
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

    # Safety backup before migrating: if a migration fails, restore with
    # 'php artisan backup:restore'. set -e aborts the deploy if the backup fails.
    php artisan backup:run

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
