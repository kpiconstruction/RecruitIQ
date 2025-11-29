# RecruitIQ Deployment (cPanel VPS)

This guide outlines a reliable, Docker-free deployment to a Linux cPanel VPS.

## Prerequisites
- PHP 8.2 with extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `intl`, `exif`
- MySQL 5.7+ or MariaDB 10.2+ (utf8mb4 support)
- Composer installed server-side or deploy `vendor/` from local
- Node 18+ (optional). If unavailable, build assets locally and upload `public/build`

## 1) Point Docroot to `public/`
- In cPanel, set the domain’s document root to the application’s `public/` directory.
- Confirm `public/.htaccess` is active.

## 2) Upload Code
- Upload the application files to the account home.
- If Composer is not available on the server, run `composer install` locally and upload the `vendor/` directory.

## 3) Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

## 4) Environment Configuration (`.env`)
- Set:
  - `APP_NAME=RecruitIQ`
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://your-domain`
  - Database: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
    - If your host requires sockets: set `DB_SOCKET` and leave `DB_HOST` blank
  - Mail (SMTP): `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`
  - `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME=RecruitIQ`
  - Sessions: `SESSION_DRIVER=database`
  - Sanctum domains: `SANCTUM_STATEFUL_DOMAINS=your-domain.com`, `SESSION_DOMAIN=your-domain.com`
- Ensure captcha keys if using Cloudflare Turnstile: `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`

## 5) Generate Key & Migrate
```bash
php artisan key:generate
php artisan migrate --force
```

## 6) Storage Symlink & Permissions
```bash
php artisan storage:link
```
- Ensure writable dirs: `storage/` and `bootstrap/cache/`
- Typical permissions: files `644`, directories `755`

## 7) Optimize Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8) Build Frontend Assets
- If Node is available on the server:
```bash
npm ci
npm run build
```
- Otherwise, build locally and upload `public/build` to the server.
- Note: `vite.config.js` refresh glob uses `app/Livewire/**` (Linux case-sensitive).

## 9) Cron Jobs
- In cPanel Cron Jobs, add:
```
* * * * * /usr/local/bin/php /home/ACCOUNT/path/to/artisan schedule:run >> /dev/null 2>&1
```
- If using queues, prefer `database` driver and run a periodic worker:
```
*/5 * * * * /usr/local/bin/php /home/ACCOUNT/path/to/artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

## 10) Optional Services
- Redis/Memcached/SQS/Beanstalkd are optional; configure `config/*.php` and `.env` only if used.
- Broadcasting (Echo/Pusher) is disabled by default; enable if required.

## 11) MySQL Charset/Collation
- `config/database.php` uses `utf8mb4` / `utf8mb4_unicode_ci`. Verify your DB supports utf8mb4.

## 12) Post-Deploy Smoke Test
- Visit `/login` (admin) and `/portal/candidate` (candidate panel) to confirm:
  - CSS/JS assets load
  - Authentication and sessions work
  - File uploads save to `storage/app/public` (check `/storage` URLs)
  - Emails send via configured SMTP

## Troubleshooting
- Blank pages: check `storage/logs/laravel.log`
- 500 errors: verify `APP_DEBUG=false` only after confirming normal operation
- Asset issues: ensure `public/build` exists and `@vite` points to built files
- Auth issues: confirm `SESSION_DOMAIN` and `SANCTUM_STATEFUL_DOMAINS` match your domain

