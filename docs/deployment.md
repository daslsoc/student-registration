# Deployment (shared server)

Production runs on a **shared server, not Docker**. Deploys happen by uploading
the project files on top of the existing install. That changes the risks: there
are **no long-running processes**, and **uploaded files are live immediately**.
Work through this list in order.

> Docker (`docker-compose.yml`, the `Makefile`) is **dev-only**. None of it
> runs in production.

## Before the first deploy

1. **PHP version.** Confirm the host runs **PHP >= 8.2** (Laravel 12 requires
   it). If it's older, you cannot deploy — get it upgraded first.
2. **Document root = `public/`.** Point the domain/subdomain docroot at the
   project's `public/` directory. If the server serves the project root
   instead, your `.env` and source become web-readable. (On cPanel-style hosts
   without a configurable docroot, use the standard "move public to web root +
   adjust paths" approach.)
3. **Production `.env` (lives on the server — never overwrite it on deploy):**
   - `APP_ENV=production`, `APP_DEBUG=false`
   - a real `APP_KEY` (`php artisan key:generate` once)
   - `SESSION_SECURE_COOKIE=true` (and HTTPS enabled/forced)
   - real MySQL creds + DB name
   - `MAIL_*` for the real mailer
   - **live** `STRIPE_SECRET`
   - all `custom.*` vars (school name, emails, pricing, WhatsApp URL, GA id) —
     see `config/custom.php`
   - `QUEUE_CONNECTION` — see step 6.

## What to upload (and what NOT to)

4. **Never upload `bootstrap/cache/*.php`.** A stale `config.php` overrides
   `.env` entirely and can pin the wrong (even production) database — this is
   the single most dangerous file to ship. Exclude `bootstrap/cache/` from the
   upload, or delete those files on the server after uploading.
5. **Build and upload front-end assets.** `public/build/` is gitignored and the
   server has no Node. Build locally and upload the result:
   ```bash
   npm ci && npm run build      # produces public/build/ (manifest + assets)
   ```
   Without it, `@vite(...)` throws "Unable to locate file in Vite manifest".
   Also upload `vendor/` (run `composer install --no-dev --optimize-autoloader`
   locally) unless the host has Composer + SSH.

## Email / queue (important — easy to miss)

6. **Emails are queued.** The confirmation / update-link / WhatsApp mailables
   are `ShouldQueue`, so they only send when something processes the queue.
   With no worker daemon on shared hosting, pick one:
   - **Simplest:** set `QUEUE_CONNECTION=sync` — mail sends inline during the
     request (slightly slower responses, no worker needed).
   - **Or:** keep a queued driver and add a cron job:
     ```
     * * * * * cd /path/to/app && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
     ```
   Without one of these, **registrations succeed but no emails are ever sent.**

## Every deploy

7. **Run after uploading files:**
   ```bash
   php artisan optimize:clear     # drop any stale compiled caches FIRST
   php artisan migrate --force    # apply new migrations to the prod MySQL DB
   php artisan config:cache       # optional: rebuild caches from the prod .env
   php artisan route:cache
   ```
   If you skip `optimize:clear`, a stale cache can silently override `.env`.
8. **Fix writable paths.** Uploading can reset permissions. The web user must
   be able to write:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
   (and ensure correct group ownership for the web server user).
9. **Storage symlink** (only if you serve user uploads): `php artisan storage:link`.

## After deploy — smoke test

- Load the home page and `/registration` over **HTTPS** (no 500s).
- Submit a test registration end-to-end through Stripe **test mode** and
  confirm: payment recorded, status `completed`, and the confirmation email
  arrives (proves the queue path works).
- Check the log isn't filling with errors: `storage/logs/laravel.log`.

## Backups

Before running any of the destructive queries in
[operations.md](operations.md) (e.g. the annual `registration_status` reset),
**take a database backup**. Keep a regular automated backup of the prod DB.

## Related

- [operations.md](operations.md) — admin SQL/PHP snippets and the annual reset.
- [security.md](security.md) — security review, fixes, and follow-ups
  (notably: add a Stripe **webhook** so payment confirmation doesn't depend on
  the browser redirect).
