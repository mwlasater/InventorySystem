# Deployment Guide - Lasater Salvage Inventory System

## Prerequisites

- DreamHost VPS or Shared Hosting with SSH access
- PHP 8.2+ with extensions: mbstring, openssl, pdo_mysql, gd, zip, xml
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (for building assets locally)

## Initial Setup

### 1. Domain & PHP Configuration

1. Log into DreamHost Panel
2. Add your domain (e.g., `inventory.lasater.com`)
3. Set PHP version to 8.2+ under **Manage Domains > PHP Version**
4. If using shared hosting, set the document root to `public/` if possible; otherwise the root `.htaccess` will redirect

### 2. Create MySQL Database

1. In DreamHost Panel, go to **MySQL Databases**
2. Create database: `lasater_inventory`
3. Create database user with full privileges
4. Note the MySQL hostname (e.g., `mysql.lasater.com`)

### 3. Clone & Configure

```bash
# SSH into your server
ssh user@server.dreamhost.com

# Clone the repository
cd ~/inventory.lasater.com
git clone https://github.com/mwlasater/InventorySystem.git .

# Copy and configure environment
cp tools/.env.production.example .env
nano .env  # Update DB credentials, APP_URL, mail settings

# Generate application key
php artisan key:generate

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php artisan migrate --force

# Seed admin user
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 4. Build Frontend Assets

Build assets locally and deploy:

```bash
# On your local machine
npm install
npm run build

# Then deploy (or use the deploy script)
./tools/deploy.sh user@server.dreamhost.com ~/inventory.lasater.com
```

### 5. Cron Job (Scheduler)

Add via DreamHost Panel or crontab:

```
* * * * * cd /home/user/inventory.lasater.com && php artisan schedule:run >> /dev/null 2>&1
```

This runs:
- `trash:purge` - Daily cleanup of items in trash > 90 days
- `backup:run` - Daily database backup

### 6. SSL Certificate

DreamHost provides free Let's Encrypt SSL. Enable it under **Manage Domains > Secure Hosting**.

## Subsequent Deployments

Use the deploy script:

```bash
./tools/deploy.sh user@server.dreamhost.com ~/inventory.lasater.com
```

Or manually:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build  # if assets changed
```

## Default Admin Credentials

- **Username:** `admin`
- **Password:** `Admin123!`

**Change the admin password immediately after first login.**

## Backup & Recovery

### Manual Backup
```bash
php artisan backup:run              # Database only
php artisan backup:run --with-media # Database + uploaded files
```

Backups are stored in `storage/app/backups/` with 30-day rotation.

### Restore from Backup
```bash
mysql -u user -p database_name < storage/app/backups/backup-YYYY-MM-DD-HHMMSS.sql
```

## Troubleshooting

### 500 Error After Deploy
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
```

### Storage Link Issues
```bash
php artisan storage:link
```

### Queue Jobs Not Processing
```bash
php artisan queue:work --tries=3
```

For persistent queue processing, consider setting up a cron-based queue runner:
```
* * * * * cd /home/user/inventory.lasater.com && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```
