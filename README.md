# Lasater Salvage Inventory

A web-based inventory management system for cataloging, organizing, and valuing a
collection of physical items — with photos, documents, transactions, QR labels,
barcode lookup, reporting, CSV import/export, multi-user roles, and a full audit trail.

See [`USER_MANUAL.md`](USER_MANUAL.md) for end-user documentation.

## Tech stack

- **Backend:** PHP 8.2+, Laravel 12, Eloquent ORM
- **Frontend:** Blade, Tailwind CSS 4, Alpine.js, Chart.js, html5-qrcode
- **Build:** Vite 7 (`laravel-vite-plugin`)
- **Database:** MySQL 8 (production and Docker dev); SQLite in-memory for tests
- **Notable packages:** `barryvdh/laravel-dompdf` (PDF reports), `simplesoftwareio/simple-qrcode`, `intervention/image-laravel`

## Environments: dev in Docker, production native

The same codebase runs in both places — only `.env` differs, so nothing in the
PHP changes between environments.

| | Local development | Production |
|---|---|---|
| Runtime | **Docker** (Laravel Sail) | **Native** on DreamHost (no Docker) |
| PHP | 8.2 container | DreamHost PHP 8.2+ |
| Database | MySQL 8.0 container | DreamHost MySQL 8 |
| Web server | Sail (built-in) | Apache + `.htaccess` |
| Frontend assets | `sail npm run dev` (Vite HMR) | pre-built `npm run build` |
| Config source | `.env` (local) | `.env` (server) |

Container versions in [`docker-compose.yml`](docker-compose.yml) are pinned to
**PHP 8.2 / MySQL 8.0** to match the DreamHost runtime, so dev doesn't silently
drift ahead of production.

Production deployment is **not** covered here — see [`DEPLOYMENT.md`](DEPLOYMENT.md).

## Local development (Docker)

**Prerequisite:** [Docker Desktop](https://www.docker.com/products/docker-desktop/).

1. **Create your env file:**

   ```bash
   cp .env.example .env
   ```

2. **Point `.env` at the Docker MySQL service.** Edit these values
   (the container reaches MySQL by its service name `mysql`, not `127.0.0.1`, and
   MySQL rejects a user literally named `root`):

   ```dotenv
   DB_HOST=mysql
   DB_DATABASE=lasater_inventory
   DB_USERNAME=sail
   DB_PASSWORD=password
   ```

3. **Install PHP dependencies** (this pulls in Sail itself). If you don't have
   PHP/Composer locally, bootstrap it through a throwaway container:

   ```bash
   docker run --rm \
     -v "$(pwd)":/var/www/html -w /var/www/html \
     laravelsail/php82-composer:latest \
     composer install --ignore-platform-reqs
   ```

   (Or just `composer install` if you have PHP 8.2+ and Composer on your host.)

4. **Start the containers:**

   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Initialize the app:**

   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

6. Visit **http://localhost**. The seeder creates a default admin
   (`admin` / `Admin123!`) — change this password immediately after first login.

> **Tip:** add a shell alias so you can type `sail` instead of `./vendor/bin/sail`:
> `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`

### Running tests

Tests use an in-memory SQLite database (configured in `phpunit.xml`), so no MySQL
is required:

```bash
./vendor/bin/sail artisan test
```

## Production

Production runs natively on DreamHost — Docker is not used there. Build assets
locally (`npm run build`) and deploy via `tools/deploy.sh`. Full instructions,
including the scheduler cron job and database backups, are in
[`DEPLOYMENT.md`](DEPLOYMENT.md).
